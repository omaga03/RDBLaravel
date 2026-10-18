<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class AccessControlSettingsController extends Controller
{
    /**
     * Display the Access Control & Download Policy settings page.
     */
    public function index()
    {
        $settings = [
            // Login & SSO
            'google_login_enabled' => SystemSetting::get('google_login_enabled', '1'),
            'google_client_id' => SystemSetting::get('google_client_id', ''),
            'google_client_secret' => SystemSetting::get('google_client_secret', ''),
            'google_allowed_domains' => SystemSetting::get('google_allowed_domains', 'pcru.ac.th'),
            'google_auto_register' => SystemSetting::get('google_auto_register', '1'),
            'standard_login_enabled' => SystemSetting::get('standard_login_enabled', '1'),

            // Download Policies
            'download_policy_full_report' => SystemSetting::get('download_policy_full_report', 'members_only'),
            'download_policy_abstract' => SystemSetting::get('download_policy_abstract', 'public'),
            'download_policy_attachments' => SystemSetting::get('download_policy_attachments', 'members_only'),
            'download_require_login_message' => SystemSetting::get('download_require_login_message', 'กรุณาเข้าสู่ระบบก่อนเพื่อดาวน์โหลดเอกสารเล่มรายงานวิจัยฉบับสมบูรณ์ ตามนโยบายความมั่นคงปลอดภัยสารสนเทศของมหาวิทยาลัย'),
            'download_logging_enabled' => SystemSetting::get('download_logging_enabled', '1'),
        ];

        $googleCallbackUrl = route('auth.google.callback');

        return view('backend.settings.access_control', compact('settings', 'googleCallbackUrl'));
    }

    /**
     * Update access control & download policy settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'download_policy_full_report' => 'required|in:public,members_only,admin_only',
            'download_policy_abstract' => 'required|in:public,members_only',
            'download_policy_attachments' => 'required|in:public,members_only,admin_only',
            'download_require_login_message' => 'required|string|max:1000',
            'google_allowed_domains' => 'nullable|string|max:255',
            'google_client_id' => 'nullable|string|max:500',
            'google_client_secret' => 'nullable|string|max:500',
        ]);

        // 1. Login & Google Settings
        SystemSetting::set('google_login_enabled', $request->has('google_login_enabled') ? '1' : '0', 'auth', 'เปิด/ปิดการเข้าสู่ระบบด้วย Google');
        SystemSetting::set('google_client_id', trim($request->google_client_id ?? ''), 'auth', 'Google OAuth Client ID');
        if ($request->filled('google_client_secret')) {
            SystemSetting::set('google_client_secret', trim($request->google_client_secret), 'auth', 'Google OAuth Client Secret');
        }
        SystemSetting::set('google_allowed_domains', trim($request->google_allowed_domains ?? ''), 'auth', 'โดเมนที่อนุญาตสำหรับ Google Login');
        SystemSetting::set('google_auto_register', $request->has('google_auto_register') ? '1' : '0', 'auth', 'สร้างบัญชีใหม่อัตโนมัติเมื่อล็อกอินด้วย Google');
        SystemSetting::set('standard_login_enabled', $request->has('standard_login_enabled') ? '1' : '0', 'auth', 'เปิด/ปิดการล็อกอินด้วย Username/Password');

        // 2. Download Security Policies
        SystemSetting::set('download_policy_full_report', $request->download_policy_full_report, 'security', 'นโยบายดาวน์โหลดรายงานวิจัยฉบับสมบูรณ์');
        SystemSetting::set('download_policy_abstract', $request->download_policy_abstract, 'security', 'นโยบายเปิดอ่านบทคัดย่อ');
        SystemSetting::set('download_policy_attachments', $request->download_policy_attachments, 'security', 'นโยบายดาวน์โหลดเอกสารแนบ');
        SystemSetting::set('download_require_login_message', $request->download_require_login_message, 'security', 'ข้อความแจ้งเตือนเมื่อต้องเข้าสู่ระบบก่อนโหลดไฟล์');
        SystemSetting::set('download_logging_enabled', $request->has('download_logging_enabled') ? '1' : '0', 'security', 'บันทึกประวัติการดาวน์โหลดเอกสาร');

        return redirect()->route('backend.settings.access_control')
            ->with('success', 'บันทึกการตั้งค่าระบบล็อกอินและเงื่อนไขความปลอดภัยในการดาวน์โหลดเอกสารเรียบร้อยแล้ว');
    }
}
