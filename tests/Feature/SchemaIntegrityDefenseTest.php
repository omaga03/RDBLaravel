<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RdbDepartment;
use App\Models\RdbPrefix;
use App\Models\RdbProject;
use App\Models\RdbProjectStatus;
use App\Models\RdbProjectPosition;
use App\Models\RdbProjectFiles;
use App\Models\RdbPublished;
use App\Models\RdbPublishedTypeAuthor;
use App\Models\RdbDip;
use App\Models\RdbDipType;
use App\Models\RdbProjectUtilize;
use App\Models\RdbProjectUtilizeType;
use App\Models\RdbResearcher;
use App\Models\RdbResearcherStatus;
use App\Models\RdbChangwat;
use App\Models\RdbDepartmentCategory;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbYear;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SchemaIntegrityDefenseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    /**
     * Test RdbDepartment smart accessors
     */
    public function test_department_accessors_and_naming()
    {
        $dept = RdbDepartment::first();
        if ($dept) {
            $this->assertNotEmpty($dept->department_nameTH);
            $this->assertEquals($dept->department_nameTH, $dept->department_name);
            $this->assertEquals($dept->department_nameTH, $dept->name);
            $this->assertEquals($dept->department_nameEN, $dept->department_name_en);
        }
    }

    /**
     * Test RdbPrefix smart accessors
     */
    public function test_prefix_accessors_and_naming()
    {
        $prefix = RdbPrefix::first();
        if ($prefix) {
            $this->assertNotEmpty($prefix->prefix_nameTH);
            $this->assertEquals($prefix->prefix_nameTH, $prefix->prefix_name);
            $this->assertEquals($prefix->prefix_nameTH, $prefix->name);
            $this->assertEquals($prefix->prefix_abbreviationTH, $prefix->abbreviation);
        }
    }

    /**
     * Test RdbProjectStatus smart accessors
     */
    public function test_project_status_accessors_and_naming()
    {
        $status = RdbProjectStatus::first();
        if ($status) {
            $this->assertNotEmpty($status->ps_name);
            $this->assertEquals($status->ps_name, $status->ps_nameTH);
            $this->assertEquals($status->ps_name, $status->status_name);
            $this->assertEquals($status->ps_name, $status->name);
        }
    }

    /**
     * Test RdbProjectPosition smart accessors
     */
    public function test_project_position_accessors_and_naming()
    {
        $pos = RdbProjectPosition::first();
        if ($pos) {
            $this->assertNotEmpty($pos->position_nameTH);
            $this->assertEquals($pos->position_nameTH, $pos->position_name);
            $this->assertEquals($pos->position_nameTH, $pos->name);
        }
    }

    /**
     * Test RdbPublishedTypeAuthor smart accessors
     */
    public function test_published_type_author_accessors_and_naming()
    {
        $type = RdbPublishedTypeAuthor::first();
        if ($type) {
            $this->assertNotEmpty($type->pubta_nameTH);
            $this->assertEquals($type->pubta_nameTH, $type->author_type);
            $this->assertEquals($type->pubta_nameTH, $type->author_type_name);
            $this->assertEquals($type->pubta_nameTH, $type->pubta_name);
            $this->assertEquals($type->pubta_nameTH, $type->name);
        }
    }

    /**
     * Test RdbDipType smart accessors
     */
    public function test_dip_type_accessors_and_naming()
    {
        $dt = RdbDipType::first();
        if ($dt) {
            $this->assertNotEmpty($dt->dipt_name);
            $this->assertEquals($dt->dipt_name, $dt->dipt_nameTH);
            $this->assertEquals($dt->dipt_name, $dt->name);
            $this->assertEquals($dt->dipt_name, $dt->type_name);
        }
    }

    /**
     * Test RdbProjectUtilizeType smart accessors
     */
    public function test_utilize_type_accessors_and_naming()
    {
        $ut = RdbProjectUtilizeType::first();
        if ($ut) {
            $this->assertNotEmpty($ut->utz_typr_name);
            $this->assertEquals($ut->utz_typr_name, $ut->utz_type_name);
            $this->assertEquals($ut->utz_typr_name, $ut->name);
            $this->assertEquals($ut->utz_typr_name, $ut->type_name);
        }
    }

    /**
     * Test RdbChangwat smart accessors
     */
    public function test_changwat_accessors_and_naming()
    {
        $cw = RdbChangwat::first();
        if ($cw) {
            $this->assertNotEmpty($cw->changwat_t);
            $this->assertEquals($cw->changwat_t, $cw->changwat_name);
            $this->assertEquals($cw->changwat_t, $cw->province_name);
            $this->assertEquals($cw->changwat_t, $cw->name);
            $this->assertEquals($cw->amphoe_t, $cw->amphoe_name);
            $this->assertEquals($cw->tambon_t, $cw->tambon_name);
        }
    }

    /**
     * Test RdbDepartmentCategory and Course accessors
     */
    public function test_department_category_and_course_accessors()
    {
        $cat = RdbDepartmentCategory::first();
        if ($cat) {
            $this->assertNotEmpty($cat->depcat_name);
            $this->assertEquals($cat->depcat_name, $cat->depcat_nameTH);
            $this->assertEquals($cat->depcat_name, $cat->name);
        }

        $course = RdbDepartmentCourse::first();
        if ($course) {
            $this->assertNotEmpty($course->cou_name);
            $this->assertEquals($course->cou_name, $course->cou_nameTH);
            $this->assertEquals($course->cou_name, $course->name);
        }
    }

    /**
     * Test RdbYear smart accessors
     */
    public function test_year_accessors_and_naming()
    {
        $year = RdbYear::first();
        if ($year) {
            $this->assertNotEmpty($year->year_name);
            $this->assertEquals($year->year_name, $year->year_nameTH);
            $this->assertEquals($year->year_name, $year->name);
        }
    }

    /**
     * Test RdbProjectFiles smart accessors
     */
    public function test_project_files_accessors_and_naming()
    {
        $file = RdbProjectFiles::first();
        if ($file) {
            $this->assertNotEmpty($file->rf_filesname);
            $this->assertEquals($file->rf_filesname, $file->file_name);
            $this->assertEquals($file->rf_filesname, $file->rf_filename);
            $this->assertEquals($file->rf_filesname, $file->name);
        }
    }

    /**
     * Test RdbResearcherStatus smart accessors
     */
    public function test_researcher_status_accessors_and_naming()
    {
        $rs = RdbResearcherStatus::first();
        if ($rs) {
            $this->assertNotEmpty($rs->restatus_name);
            $this->assertEquals($rs->restatus_name, $rs->rst_name);
            $this->assertEquals($rs->restatus_name, $rs->name);
            $this->assertEquals($rs->restatus_name, $rs->status_name);
        }
    }

    /**
     * Test RdbProject smart accessors and relation aliases
     */
    public function test_project_accessors_and_relations()
    {
        $p = RdbProject::first();
        if ($p) {
            $this->assertNotEmpty($p->pro_nameTH);
            $this->assertEquals($p->pro_nameTH, $p->pro_name_th);
            $this->assertEquals($p->pro_nameTH, $p->title);
            $this->assertEquals($p->pro_nameTH, $p->project_name);
            $this->assertEquals($p->pro_abstract, $p->pro_detail);
            $this->assertEquals($p->pro_date_start, $p->pro_start);
            $this->assertEquals($p->pro_date_end, $p->pro_end);

            // Relation aliases
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $p->projectStatus());
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $p->projectFiles());
        }
    }

    /**
     * Test RdbPublished smart accessors
     */
    public function test_published_accessors_and_naming()
    {
        $pub = RdbPublished::first();
        if ($pub) {
            $this->assertNotEmpty($pub->pub_name);
            $this->assertEquals($pub->pub_name, $pub->pub_nameTH);
            $this->assertEquals($pub->pub_name, $pub->pub_name_th);
            $this->assertEquals($pub->pub_name, $pub->title);
            $this->assertEquals($pub->pub_name, $pub->published_name);
            $this->assertEquals($pub->pf_path, $pub->pub_file_path);
        }
    }

    /**
     * Test RdbDip smart accessors
     */
    public function test_dip_accessors_and_naming()
    {
        $dip = RdbDip::first();
        if ($dip) {
            $this->assertEquals($dip->dip_data2_name, $dip->dip_name);
            $this->assertEquals($dip->dip_data2_name, $dip->dip_nameTH);
            $this->assertEquals($dip->dip_data2_name, $dip->title);
            $this->assertEquals($dip->dip_data2_name, $dip->name);
        }
    }

    /**
     * Test RdbResearcher smart accessors
     */
    public function test_researcher_accessors_and_naming()
    {
        $r = RdbResearcher::with('prefix')->first();
        if ($r) {
            $this->assertNotEmpty($r->researcher_fname);
            $this->assertEquals($r->full_name, $r->name);
            $this->assertEquals($r->full_name, $r->researcher_name);
            $this->assertEquals('อาจารย์ / นักวิจัย', $r->position_academic);
            $this->assertIsString($r->full_name_en);
        }
    }

    /**
     * Test User name accessor with prefix defense
     */
    public function test_user_name_accessor_and_prefix()
    {
        $userWithResearcher = User::whereNotNull('researcher_id')
            ->whereHas('researcher', fn($q) => $q->whereNotNull('prefix_id'))
            ->with('researcher.prefix')
            ->first();

        $this->assertNotNull($userWithResearcher, 'A user with researcher and prefix should exist');
        if ($userWithResearcher && $userWithResearcher->researcher && $userWithResearcher->researcher->prefix) {
            $expectedPrefix = $userWithResearcher->researcher->prefix->prefix_nameTH;
            $this->assertStringStartsWith($expectedPrefix, $userWithResearcher->name);
        }
    }
}
