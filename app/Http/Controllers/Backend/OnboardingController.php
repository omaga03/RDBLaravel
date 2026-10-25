<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RdbDepartment;
use App\Models\RdbPrefix;
use App\Models\User;

class OnboardingController extends Controller
{
    /**
     * Display the identity verification & department selection screen.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // If user already has full backend access, redirect to dashboard
        if ($user->canAccessFullBackend()) {
            return redirect()->route('backend.site.index');
        }

        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $prefixes = RdbPrefix::orderBy('prefix_id')->get();

        return view('backend.onboarding.index', compact('user', 'departments', 'prefixes'));
    }

    /**
     * Store or update the user's onboarding verification submission.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->canAccessFullBackend()) {
            return redirect()->route('backend.site.index');
        }

        $validated = $request->validate([
            'onboarding_department_id' => 'required|exists:rdb_department,department_id',
            'onboarding_prefix_id' => 'required|exists:rdb_prefix,prefix_id',
            'onboarding_fname' => 'required|string|max:100',
            'onboarding_lname' => 'required|string|max:100',
            'onboarding_fname_en' => 'nullable|string|max:100',
            'onboarding_lname_en' => 'nullable|string|max:100',
            'onboarding_tel' => 'nullable|string|max:50',
            'onboarding_position' => 'nullable|string|max:100',
        ], [
            'onboarding_department_id.required' => 'กรุณาเลือกสังกัด / คณะ / หน่วยงาน',
            'onboarding_prefix_id.required' => 'กรุณาเลือกคำนำหน้าชื่อ',
            'onboarding_fname.required' => 'กรุณาระบุชื่อ (ภาษาไทย)',
            'onboarding_lname.required' => 'กรุณาระบุนามสกุล (ภาษาไทย)',
        ]);

        $user->update([
            'onboarding_department_id' => $validated['onboarding_department_id'],
            'onboarding_prefix_id' => $validated['onboarding_prefix_id'],
            'onboarding_fname' => trim($validated['onboarding_fname']),
            'onboarding_lname' => trim($validated['onboarding_lname']),
            'onboarding_fname_en' => !empty($validated['onboarding_fname_en']) ? trim($validated['onboarding_fname_en']) : null,
            'onboarding_lname_en' => !empty($validated['onboarding_lname_en']) ? trim($validated['onboarding_lname_en']) : null,
            'onboarding_tel' => !empty($validated['onboarding_tel']) ? trim($validated['onboarding_tel']) : null,
            'onboarding_position' => !empty($validated['onboarding_position']) ? trim($validated['onboarding_position']) : null,
            'onboarding_submitted_at' => now(),
            'onboarding_status' => 'pending',
            'onboarding_reject_reason' => null,
        ]);

        return redirect()->route('backend.onboarding.index')
            ->with('success', 'บันทึกและส่งข้อมูลยืนยันตัวตนเรียบร้อยแล้ว คำขอของท่านอยู่ระหว่างรอผู้ดูแลระบบ (Admin) ตรวจสอบและปลดล็อกการใช้งาน');
    }
}
