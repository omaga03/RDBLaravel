<?php

namespace Tests\Feature\Backend;

use App\Models\AuthAssignment;
use App\Models\RdbProject;
use App\Models\RdbProjectWork;
use App\Models\RdbResearcher;
use App\Models\RdbReviewAssignment;
use App\Models\RdbReviewClarification;
use App\Models\RdbReviewCriteria;
use App\Models\RdbReviewScore;
use App\Models\RdbReviewSubmission;
use App\Models\RdbReviewTemplate;
use App\Models\User;
use App\Notifications\ReviewAssignedNotification;
use App\Notifications\ReviewFinalApprovedNotification;
use App\Notifications\ReviewRoundClosedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectPeerReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    protected function createTestResearcher(string $fname, string $lname): RdbResearcher
    {
        $maxId = (int) RdbResearcher::max('researcher_id');
        $res = new RdbResearcher();
        $res->researcher_id = (string) ($maxId + rand(100, 9999));
        $res->prefix_id = 1;
        $res->department_id = 1;
        $res->researcher_fname = $fname;
        $res->researcher_lname = $lname;
        $res->researcher_fnameEN = 'Test';
        $res->researcher_lnameEN = 'Researcher';
        $res->researcher_email = strtolower(Str::random(8)) . '@pcru.ac.th';
        $res->restatus_id = 1;
        $res->save();

        return $res;
    }

    protected function createTestUser(RdbResearcher $researcher, string $role = 'Researcher'): User
    {
        $user = new User();
        $user->username = 'test_rev_' . Str::lower(Str::random(8));
        $user->email = $researcher->researcher_email;
        $user->password_hash = Hash::make('password123');
        $user->auth_key = Str::random(32);
        $user->confirmed_at = time();
        $user->status = 10;
        $user->onboarding_status = 'verified';
        $user->researcher_id = $researcher->researcher_id;
        $user->save();

        AuthAssignment::create([
            'item_name' => $role,
            'user_id' => $user->id,
            'created_at' => time(),
        ]);

        return $user;
    }

    protected function getValidPdfBinary(): string
    {
        return "%PDF-1.4\n"
            . "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            . "2 0 obj << /Type /Pages /Kids [3 0 R 4 0 R] /Count 2 >> endobj\n"
            . "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >> endobj\n"
            . "4 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >> endobj\n"
            . "xref\n0 5\n0000000000 65535 f \n"
            . "trailer << /Size 5 /Root 1 0 R >>\nstartxref\n0\n%%EOF";
    }

    public function test_form_template_designer_and_authorization(): void
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        $resModel = $this->createTestResearcher('สมชาย', 'นักวิจัยทั่วไป');
        $researcherUser = $this->createTestUser($resModel, 'Researcher');

        // 1. Admin can view templates page and default templates are seeded
        $viewRes = $this->actingAs($admin)->get(route('backend.project_review.templates'));
        $viewRes->assertOk();
        $this->assertGreaterThanOrEqual(3, RdbReviewTemplate::count());

        // 1.1 Dedicated Create & Edit Pages
        $this->actingAs($admin)->get(route('backend.project_review.templates.create'))->assertOk();
        $this->actingAs($researcherUser)->get(route('backend.project_review.templates.create'))->assertStatus(403);

        // 2. Non-admin researcher gets 403 when trying to view/create/update templates
        $this->actingAs($researcherUser)
            ->get(route('backend.project_review.templates'))
            ->assertStatus(403);

        $this->actingAs($researcherUser)
            ->post(route('backend.project_review.templates.store'), [
                'title' => 'Unauthorized Template',
                'evaluation_mode' => 'hybrid',
                'criteria' => [
                    ['name' => 'Criterion 1', 'item_type' => 'scale', 'weight' => 100],
                ],
            ])
            ->assertStatus(403);

        // 3. Admin creates a new custom evaluation template with scale, pass_fail, open_ended, and hybrid criteria
        $storeRes = $this->actingAs($admin)->post(route('backend.project_review.templates.store'), [
            'title' => 'แบบประเมินเฉพาะทางทดสอบระบบ Peer Review',
            'description' => 'คำชี้แจงสำหรับการทดสอบระบบ',
            'evaluation_mode' => 'hybrid',
            'scale_max' => 5,
            'pass_threshold' => 75,
            'is_active' => 1,
            'is_default' => 0,
            'criteria' => [
                [
                    'category' => 'หมวดที่ 1',
                    'name' => 'ความสอดคล้องของวัตถุประสงค์',
                    'description' => 'วัดระดับสเกลแบบ 25 คะแนน ห่างทีละ 5',
                    'item_type' => 'scale',
                    'scale_max' => 25,
                    'scale_step' => 5,
                    'weight' => 40,
                    'is_required' => 1,
                ],
                [
                    'category' => 'หมวดที่ 1',
                    'name' => 'ความสมบูรณ์ของระเบียบวิธีวิจัย',
                    'description' => 'วัดระดับผสมผสาน',
                    'item_type' => 'hybrid',
                    'weight' => 40,
                    'is_required' => 1,
                ],
                [
                    'category' => 'หมวดที่ 2',
                    'name' => 'จริยธรรมการวิจัยในมนุษย์',
                    'description' => 'ผ่านหรือไม่ผ่าน',
                    'item_type' => 'pass_fail',
                    'weight' => 20,
                    'is_required' => 1,
                ],
                [
                    'category' => 'หมวดที่ 3',
                    'name' => 'ข้อเสนอแนะเพื่อการนำไปใช้ประโยชน์',
                    'description' => 'ปลายเปิด',
                    'item_type' => 'open_ended',
                    'weight' => 0,
                    'is_required' => 0,
                ],
            ],
        ]);

        $storeRes->assertRedirect(route('backend.project_review.templates'));
        $template = RdbReviewTemplate::where('title', 'แบบประเมินเฉพาะทางทดสอบระบบ Peer Review')->with('criteria')->first();
        $this->assertNotNull($template);
        $this->assertCount(4, $template->criteria);
        $this->assertEquals(['scale', 'hybrid', 'pass_fail', 'open_ended'], $template->criteria->pluck('item_type')->toArray());

        // Verify Scale Options computation: max 25, step 5 => [5, 10, 15, 20, 25]
        $scaleCrit = $template->criteria->firstWhere('item_type', 'scale');
        $this->assertEquals(25, $scaleCrit->scale_max);
        $this->assertEquals(5, $scaleCrit->scale_step);
        $this->assertEquals([5, 10, 15, 20, 25], $scaleCrit->scale_options);

        // Edit page authorization
        $this->actingAs($admin)->get(route('backend.project_review.templates.edit', $template->id))->assertOk();
        $this->actingAs($researcherUser)->get(route('backend.project_review.templates.edit', $template->id))->assertStatus(403);

        // 4. Non-admin gets 403 on updateTemplate
        $this->actingAs($researcherUser)
            ->put(route('backend.project_review.templates.update', $template->id), [
                'title' => 'Hacked Title',
                'evaluation_mode' => 'hybrid',
            ])
            ->assertStatus(403);

        // 5. Admin updates the template
        $firstCriterionId = $template->criteria->first()->id;
        $updateRes = $this->actingAs($admin)->put(route('backend.project_review.templates.update', $template->id), [
            'title' => 'แบบประเมินเฉพาะทางทดสอบระบบ (แก้ไขแล้ว)',
            'description' => 'อัปเดตคำอธิบาย',
            'evaluation_mode' => 'hybrid',
            'scale_max' => 5,
            'pass_threshold' => 80,
            'is_active' => 1,
            'criteria' => [
                [
                    'id' => $firstCriterionId,
                    'category' => 'หมวดที่ 1',
                    'name' => 'ความสอดคล้องของวัตถุประสงค์ (ปรับปรุง)',
                    'item_type' => 'scale',
                    'weight' => 50,
                ],
                [
                    'category' => 'หมวดที่ 2',
                    'name' => 'ความสมบูรณ์ของเนื้อหา',
                    'item_type' => 'hybrid',
                    'weight' => 50,
                ],
            ],
        ]);

        $updateRes->assertRedirect(route('backend.project_review.templates'));
        $template->refresh();
        $this->assertEquals('แบบประเมินเฉพาะทางทดสอบระบบ (แก้ไขแล้ว)', $template->title);
        $this->assertEquals(80.00, (float) $template->pass_threshold);
        $this->assertCount(2, $template->criteria);
    }

    public function test_end_to_end_single_blind_peer_review_workflow_and_security(): void
    {
        Storage::fake('public');
        Notification::fake();

        $admin = User::find(129);
        $this->assertNotNull($admin);

        // Create Project Owner & Project
        $ownerRes = $this->createTestResearcher('ดร.อาทิตย์', 'เจ้าของโครงการวิจัย');
        $ownerUser = $this->createTestUser($ownerRes, 'Researcher');

        // Create 3 Secret Internal Reviewers
        $rev1Res = $this->createTestResearcher('ศ.ดร.กิตติศักดิ์', 'ผู้ประเมินความลับหนึ่ง');
        $rev1User = $this->createTestUser($rev1Res, 'Researcher');

        $rev2Res = $this->createTestResearcher('รศ.ดร.นภัสสร', 'ผู้ประเมินความลับสอง');
        $rev2User = $this->createTestUser($rev2Res, 'Researcher');

        $rev3Res = $this->createTestResearcher('ผศ.ดร.ธนากร', 'ผู้ประเมินความลับสาม');
        $rev3User = $this->createTestUser($rev3Res, 'Researcher');

        // Create Unrelated Researcher for IDOR boundary testing
        $outsiderRes = $this->createTestResearcher('นายคนนอก', 'ไม่มีสิทธิ์เข้าถึง');
        $outsiderUser = $this->createTestUser($outsiderRes, 'Researcher');

        // Create a test project owned by $ownerUser
        $project = RdbProject::create([
            'pro_code' => 'PCRU-REV-' . rand(1000, 9999),
            'pro_nameTH' => 'โครงการวิจัยทดสอบระบบประเมินเล่มรายงานวิจัยแบบปกปิดรายชื่อผู้ทรงคุณวุฒิ',
            'pro_nameEN' => 'Single-Blind Peer Review Subsystem Verification Project',
            'year_id' => 18,
            'department_id' => 1,
            'pt_id' => 1,
            'ps_id' => 1,
            'pro_budget' => 150000,
            'data_show' => 1,
            'user_created' => $ownerUser->id,
        ]);

        RdbProjectWork::create([
            'pro_id' => $project->pro_id,
            'researcher_id' => $ownerRes->researcher_id,
            'position_id' => 1,
            'ratio' => 100,
        ]);

        // Ensure default templates exist and pick the default hybrid template
        $this->actingAs($admin)->get(route('backend.project_review.index'))->assertOk();
        $template = RdbReviewTemplate::with('criteria')->where('is_default', true)->first()
            ?? RdbReviewTemplate::with('criteria')->first();
        $this->assertNotNull($template);

        // =========================================================================
        // STEP 2: Report Upload & Page Count Test
        // =========================================================================
        $draftPdf = UploadedFile::fake()->createWithContent('Draft_Report_Round1.pdf', $this->getValidPdfBinary());

        $submitRes = $this->actingAs($ownerUser)->post(route('backend.project_review.submit_report', $project->pro_id), [
            'submission_type' => 'initial_draft',
            'submission_title' => 'ร่างรายงานวิจัยฉบับสมบูรณ์ (รอบที่ 1)',
            'report_file' => $draftPdf,
            'note' => 'ส่งร่างรายงานฉบับสมบูรณ์เพื่อขอรับการประเมินรอบแรก',
        ]);

        $submitRes->assertRedirect(route('backend.project_review.project', $project->pro_id));
        $submission = RdbReviewSubmission::where('pro_id', $project->pro_id)->where('round_number', 1)->first();
        $this->assertNotNull($submission);
        $this->assertEquals(1, $submission->round_number);
        $this->assertStringStartsWith('reviews/', $submission->file_path);
        $this->assertGreaterThan(0, (int) $submission->page_count);
        $this->assertEquals('submitted', $submission->status);

        // =========================================================================
        // STEP 3: Reviewer Assignment (1..N) & Conflict-of-Interest Guard Test
        // =========================================================================
        // Admin attempts to assign Project Owner (Conflict of Interest!) + Reviewer 1 + Reviewer 2
        $assignRes = $this->actingAs($admin)->post(route('backend.project_review.assign', $submission->id), [
            'template_id' => $template->id,
            'reviewer_ids' => [
                $ownerUser->id, // Conflict of Interest: Project Owner must be skipped!
                $rev1User->id,
                $rev2User->id,
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $assignRes->assertRedirect(route('backend.project_review.project', $project->pro_id));

        $assignments = RdbReviewAssignment::where('submission_id', $submission->id)->orderBy('id')->get();
        // Only 2 assignments must be created (owner skipped due to Conflict of Interest guard)
        $this->assertCount(2, $assignments);
        $this->assertFalse($assignments->contains('reviewer_id', $ownerUser->id));
        $this->assertTrue($assignments->contains('reviewer_id', $rev1User->id));
        $this->assertTrue($assignments->contains('reviewer_id', $rev2User->id));

        Notification::assertSentTo($rev1User, ReviewAssignedNotification::class);
        Notification::assertSentTo($rev2User, ReviewAssignedNotification::class);

        $submission->refresh();
        $this->assertEquals('under_review', $submission->status);

        $assignment1 = $assignments->firstWhere('reviewer_id', $rev1User->id);
        $assignment2 = $assignments->firstWhere('reviewer_id', $rev2User->id);

        // =========================================================================
        // STEP 4: Accept / Decline Flow Test
        // =========================================================================
        // Reviewer 2 declines the assignment with a reason
        $declineReason = 'ติดภาระงานสอนและวิจัยเร่งด่วน';
        $declineRes = $this->actingAs($rev2User)->post(route('backend.project_review.respond', $assignment2->id), [
            'decision' => 'declined',
            'decline_reason' => $declineReason,
        ]);
        $declineRes->assertRedirect(route('backend.project_review.my_reviews'));

        $assignment2->refresh();
        $this->assertEquals('declined', $assignment2->status);
        $this->assertEquals($declineReason, $assignment2->decline_reason);

        // Admin assigns Reviewer 3 as a replacement
        $this->actingAs($admin)->post(route('backend.project_review.assign', $submission->id), [
            'template_id' => $template->id,
            'reviewer_ids' => [$rev3User->id],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ])->assertRedirect(route('backend.project_review.project', $project->pro_id));

        $assignment3 = RdbReviewAssignment::where('submission_id', $submission->id)
            ->where('reviewer_id', $rev3User->id)
            ->first();
        $this->assertNotNull($assignment3);

        // Reviewer 1 and Reviewer 3 accept their assignments
        $this->actingAs($rev1User)->post(route('backend.project_review.respond', $assignment1->id), [
            'decision' => 'accepted',
        ])->assertRedirect(route('backend.project_review.evaluate', $assignment1->id));

        $this->actingAs($rev3User)->post(route('backend.project_review.respond', $assignment3->id), [
            'decision' => 'accepted',
        ])->assertRedirect(route('backend.project_review.evaluate', $assignment3->id));

        $assignment1->refresh();
        $assignment3->refresh();
        $this->assertEquals('accepted', $assignment1->status);
        $this->assertEquals('accepted', $assignment3->status);

        // =========================================================================
        // STEP 5: Evaluation Submission Test
        // =========================================================================
        $criteriaInputs1 = [];
        $criteriaInputs3 = [];
        foreach ($template->criteria as $crit) {
            $criteriaInputs1[$crit->id] = [
                'score' => 4,
                'pass_status' => 'pass',
                'comment' => 'ข้อเสนอแนะรายข้อจากคนที่ 1 สำหรับ ' . $crit->name,
            ];
            $criteriaInputs3[$crit->id] = [
                'score' => 5,
                'pass_status' => 'pass',
                'comment' => 'ข้อเสนอแนะรายข้อจากคนที่ 2 สำหรับ ' . $crit->name,
            ];
        }

        $generalComment1 = 'ระเบียบวิธีวิจัยมีความชัดเจน แต่ควรเพิ่มตารางเปรียบเทียบผลในบทที่ 4';
        $generalComment3 = 'งานวิจัยมีคุณค่าทางวิชาการสูง เห็นควรปรับแก้คำผิดเล็กน้อยในบทสรุป';

        $annotatedPdf1 = UploadedFile::fake()->createWithContent(
            'Secret_Reviewer1_Name_Comments.pdf',
            $this->getValidPdfBinary()
        );

        $evalRes1 = $this->actingAs($rev1User)->post(route('backend.project_review.submit_evaluation', $assignment1->id), [
            'result_decision' => 'minor_revision',
            'general_comment' => $generalComment1,
            'criteria' => $criteriaInputs1,
            'annotated_file' => $annotatedPdf1,
        ]);
        $evalRes1->assertRedirect(route('backend.project_review.evaluate', $assignment1->id));

        $evalRes3 = $this->actingAs($rev3User)->post(route('backend.project_review.submit_evaluation', $assignment3->id), [
            'result_decision' => 'minor_revision',
            'general_comment' => $generalComment3,
            'criteria' => $criteriaInputs3,
        ]);
        $evalRes3->assertRedirect(route('backend.project_review.evaluate', $assignment3->id));

        $assignment1->refresh();
        $assignment3->refresh();
        $this->assertEquals('completed', $assignment1->status);
        $this->assertEquals('completed', $assignment3->status);
        $this->assertGreaterThan(0, (float) $assignment1->total_score);
        $this->assertGreaterThan(0, (float) $assignment1->score_percentage);
        $this->assertGreaterThan(0, RdbReviewScore::where('assignment_id', $assignment1->id)->count());
        $this->assertEquals('Secret_Reviewer1_Name_Comments.pdf', $assignment1->annotated_file_name);

        // Before round is closed, researcher cannot download annotated file yet (403)
        $this->actingAs($ownerUser)
            ->get(route('backend.project_review.assignment.annotated_file', $assignment1->id))
            ->assertStatus(403);

        // =========================================================================
        // STEP 6: Close Round & Single-Blind Anonymity Verification Test
        // =========================================================================
        $adminSummaryText = 'เห็นควรแก้ไขตามข้อเสนอแนะของผู้ทรงคุณวุฒิทั้ง 2 ท่าน';
        $closeRes = $this->actingAs($admin)->post(route('backend.project_review.close_round', $submission->id), [
            'next_status' => 'revision_required',
            'admin_summary' => $adminSummaryText,
        ]);
        $closeRes->assertRedirect(route('backend.project_review.project', $project->pro_id));

        Notification::assertSentTo($ownerUser, ReviewRoundClosedNotification::class);

        // Researcher visits GET /backend/project-reviews (Index)
        $indexRes = $this->actingAs($ownerUser)->get(route('backend.project_review.index'));
        $indexRes->assertOk();
        $indexRes->assertDontSee($rev1Res->researcher_fname);
        $indexRes->assertDontSee($rev1Res->researcher_lname);
        $indexRes->assertDontSee($rev2Res->researcher_fname);
        $indexRes->assertDontSee($rev3Res->researcher_fname);
        $indexRes->assertDontSee($rev3Res->researcher_lname);

        // Verify data minimization on index view assignments collection for non-admin
        $viewSubmissions = $indexRes->viewData('submissions');
        $firstSubInView = $viewSubmissions->firstWhere('id', $submission->id);
        $this->assertNotNull($firstSubInView);
        foreach ($firstSubInView->assignments as $loadedAsgn) {
            $attributes = $loadedAsgn->getAttributes();
            $this->assertArrayNotHasKey('reviewer_id', $attributes);
            $this->assertArrayNotHasKey('researcher_id', $attributes);
            $this->assertFalse($loadedAsgn->relationLoaded('reviewer'));
            $this->assertFalse($loadedAsgn->relationLoaded('researcher'));
        }

        // Researcher visits GET /backend/project-reviews/project/{pro_id}
        $projectViewRes = $this->actingAs($ownerUser)->get(route('backend.project_review.project', $project->pro_id));
        $projectViewRes->assertOk();
        $projectViewRes->assertSee('ผู้ทรงคุณวุฒิคนที่ 1');
        $projectViewRes->assertSee('ผู้ทรงคุณวุฒิคนที่ 2');
        $projectViewRes->assertSee($generalComment1);
        $projectViewRes->assertSee($generalComment3);
        $projectViewRes->assertSee($adminSummaryText);

        // CRITICAL SINGLE-BLIND: Never leak reviewer names or usernames!
        $projectViewRes->assertDontSee($rev1Res->researcher_fname);
        $projectViewRes->assertDontSee($rev1Res->researcher_lname);
        $projectViewRes->assertDontSee($rev1User->username);
        $projectViewRes->assertDontSee($rev2Res->researcher_fname);
        $projectViewRes->assertDontSee($rev2Res->researcher_lname);
        $projectViewRes->assertDontSee($rev3Res->researcher_fname);
        $projectViewRes->assertDontSee($rev3Res->researcher_lname);
        $projectViewRes->assertDontSee($rev3User->username);

        // Researcher downloads the annotated file
        $annotatedDownloadRes = $this->actingAs($ownerUser)
            ->get(route('backend.project_review.assignment.annotated_file', $assignment1->id));
        $annotatedDownloadRes->assertOk();
        $disposition = $annotatedDownloadRes->headers->get('Content-Disposition');
        $this->assertStringContainsString("reviewer_feedback_{$assignment1->id}.pdf", $disposition);
        $this->assertStringNotContainsString('Secret_Reviewer1_Name_Comments.pdf', $disposition);

        // =========================================================================
        // STEP 7: Clarification / Dispute & Final Report Approval Sync Test
        // =========================================================================
        $clarificationPdf = UploadedFile::fake()->createWithContent('Response_Letter.pdf', $this->getValidPdfBinary());
        $clarRes = $this->actingAs($ownerUser)->post(route('backend.project_review.clarification.store', $submission->id), [
            'action_type' => 'dispute',
            'message' => 'ได้ดำเนินการปรับปรุงตารางในบทที่ 4 และแนบตารางเปรียบเทียบการแก้ไขมาพร้อมนี้แล้วครับ',
            'attachment' => $clarificationPdf,
        ]);
        $clarRes->assertRedirect(route('backend.project_review.project', $project->pro_id));

        $submission->refresh();
        $this->assertEquals('disputed', $submission->status);

        $clarification = RdbReviewClarification::where('submission_id', $submission->id)->first();
        $this->assertNotNull($clarification);

        // Verify clarification attachment controller download route works for owner and blocks outsider
        $this->actingAs($ownerUser)
            ->get(route('backend.project_review.clarification.attachment', $clarification->id))
            ->assertOk();
        $this->actingAs($outsiderUser)
            ->get(route('backend.project_review.clarification.attachment', $clarification->id))
            ->assertStatus(403);

        // Researcher uploads the revised final_report PDF (Round 2)
        $finalPdf = UploadedFile::fake()->createWithContent('Final_Complete_Report.pdf', $this->getValidPdfBinary());
        $finalSubmitRes = $this->actingAs($ownerUser)->post(route('backend.project_review.submit_report', $project->pro_id), [
            'submission_type' => 'final_report',
            'submission_title' => 'เล่มรายงานวิจัยฉบับสมบูรณ์ขั้นสุดท้าย (รอบที่ 2)',
            'report_file' => $finalPdf,
            'note' => 'แก้ไขครบถ้วนตามมติผู้ทรงคุณวุฒิทั้ง 2 ท่าน',
        ]);
        $finalSubmitRes->assertRedirect(route('backend.project_review.project', $project->pro_id));

        $finalSubmission = RdbReviewSubmission::where('pro_id', $project->pro_id)->where('round_number', 2)->first();
        $this->assertNotNull($finalSubmission);
        $this->assertEquals('final_submitted', $finalSubmission->status);

        // Set project todspace to 1 before approval to verify it resets to 0 on final approval
        $project->todspace = 1;
        $project->save();

        // Admin approves the final report
        $approveRes = $this->actingAs($admin)->post(route('backend.project_review.approve_final', $finalSubmission->id), [
            'admin_summary' => 'ตรวจสอบความครบถ้วนสมบูรณ์แล้ว อนุมัติเล่มรายงานวิจัยฉบับสมบูรณ์',
        ]);
        $approveRes->assertRedirect(route('backend.project_review.project', $project->pro_id));

        Notification::assertSentTo($ownerUser, ReviewFinalApprovedNotification::class);

        $finalSubmission->refresh();
        $project->refresh();

        $this->assertEquals('approved', $finalSubmission->status);
        $this->assertNotEmpty($project->pro_file);
        $this->assertEquals(basename($finalSubmission->file_path), $project->pro_file);
        $this->assertStringStartsWith('reports/', $project->pro_file_path);
        $this->assertGreaterThan(0, (int) $project->pro_page_num);
        $this->assertSame(0, (int) $project->todspace);

        // =========================================================================
        // STEP 8: IDOR & Authorization Boundary Test
        // =========================================================================
        // Unrelated researcher cannot view project review page (403)
        $this->actingAs($outsiderUser)
            ->get(route('backend.project_review.project', $project->pro_id))
            ->assertStatus(403);

        // Unrelated researcher cannot upload report to someone else's project (403)
        $this->actingAs($outsiderUser)
            ->post(route('backend.project_review.submit_report', $project->pro_id), [
                'submission_type' => 'initial_draft',
                'submission_title' => 'Unauthorized Upload',
                'report_file' => UploadedFile::fake()->createWithContent('hack.pdf', $this->getValidPdfBinary()),
            ])
            ->assertStatus(403);

        // Unrelated researcher cannot view or submit someone else's evaluation assignment (403)
        $this->actingAs($outsiderUser)
            ->get(route('backend.project_review.evaluate', $assignment1->id))
            ->assertStatus(403);

        $this->actingAs($outsiderUser)
            ->post(route('backend.project_review.submit_evaluation', $assignment1->id), [
                'result_decision' => 'pass',
                'general_comment' => 'Unauthorized Evaluation',
            ])
            ->assertStatus(403);

        // Unrelated researcher (and even project owner) cannot close round or approve final report (403)
        $this->actingAs($outsiderUser)
            ->post(route('backend.project_review.close_round', $submission->id), [
                'next_status' => 'round_closed',
            ])
            ->assertStatus(403);

        $this->actingAs($ownerUser)
            ->post(route('backend.project_review.close_round', $submission->id), [
                'next_status' => 'round_closed',
            ])
            ->assertStatus(403);

        $this->actingAs($outsiderUser)
            ->post(route('backend.project_review.approve_final', $finalSubmission->id))
            ->assertStatus(403);

        $this->actingAs($ownerUser)
            ->post(route('backend.project_review.approve_final', $finalSubmission->id))
            ->assertStatus(403);
    }
}
