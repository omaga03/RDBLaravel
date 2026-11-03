<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RestoreOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $filename;
    public string $ipAddress;
    public string $userAgent;
    public string $creatorName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $filename, string $ipAddress, string $userAgent, string $creatorName)
    {
        $this->otp = $otp;
        $this->filename = $filename;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->creatorName = $creatorName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[CRITICAL SAFETY] รหัส OTP ยืนยันการกู้คืนระบบฐานข้อมูลงานวิจัย มรภ.เพชรบูรณ์')
                    ->html($this->renderHtml());
    }

    protected function renderHtml(): string
    {
        $now = now()->addYears(543)->translatedFormat('d F Y H:i:s');
        return <<<HTML
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Sarabun', Arial, sans-serif; line-height: 1.6; color: #1E293B; background: #F8FAFC; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #DC2626; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { color: #DC2626; margin: 0; font-size: 20px; }
        .badge { display: inline-block; background: #FEE2E2; color: #B91C1C; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 13px; margin-bottom: 10px; }
        .otp-box { background: #0F172A; color: #38BDF8; font-size: 32px; letter-spacing: 8px; font-weight: bold; text-align: center; padding: 18px; border-radius: 8px; margin: 25px 0; font-family: Consolas, monospace; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #F1F5F9; }
        .meta-table td:first-child { font-weight: bold; color: #64748B; width: 35%; }
        .footer { font-size: 12px; color: #94A3B8; text-align: center; margin-top: 25px; border-top: 1px solid #F1F5F9; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="badge">⚠️ SECURITY ACTION: DATABASE RESTORE REQUEST</span>
            <h2>การร้องขอกู้คืนฐานข้อมูลระบบ (System Restore Request)</h2>
        </div>
        <p>เรียน <strong>{$this->creatorName} (The Creator)</strong>,</p>
        <p>ระบบตรวจพบการส่งคำร้องขอ <strong>กู้คืนข้อมูลระบบ (Database Restore)</strong> ซึ่งเป็นขั้นตอนสำคัญระดับสูงสุด (ระบบจะทำการสร้าง Safety Snapshot ฐานข้อมูลปัจจุบันสำรองไว้ก่อนดำเนินการกู้คืนเสมอ)</p>
        
        <p>กรุณานำรหัสความปลอดภัย OTP 6 หลักด้านล่างนี้ไปกรอกในหน้าต่างยืนยันบนระบบ:</p>
        
        <div class="otp-box">{$this->otp}</div>
        
        <p style="text-align: center; color: #DC2626; font-size: 13px;">(รหัส OTP นี้มีอายุการใช้งาน 10 นาที และใช้ได้เพียงครั้งเดียวเท่านั้น)</p>
        
        <table class="meta-table">
            <tr>
                <td>ไฟล์สำรองเป้าหมาย:</td>
                <td><code>{$this->filename}</code></td>
            </tr>
            <tr>
                <td>วัน-เวลาที่ร้องขอ:</td>
                <td>{$now} น.</td>
            </tr>
            <tr>
                <td>IP Address ผู้ร้องขอ:</td>
                <td>{$this->ipAddress}</td>
            </tr>
            <tr>
                <td>อุปกรณ์ / Browser:</td>
                <td>{$this->userAgent}</td>
            </tr>
        </table>
        
        <p style="font-size: 13px; color: #64748B;">
            * หากท่านไม่ได้เป็นผู้ทำรายการนี้ โปรดตรวจสอบความปลอดภัยของบัญชีผู้ดูแลระบบทันที และห้ามเปิดเผยรหัส OTP นี้แก่บุคคลอื่น
        </p>
        
        <div class="footer">
            ระบบบริหารจัดการงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU Research Database)<br>
            ข้อความแจ้งเตือนอัตโนมัติจากระบบรักษาความปลอดภัย
        </div>
    </div>
</body>
</html>
HTML;
    }
}
