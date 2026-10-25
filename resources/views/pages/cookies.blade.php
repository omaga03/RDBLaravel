@extends('layouts.app')
@section('title', 'นโยบายคุกกี้ - ระบบฐานข้อมูลงานวิจัย มรภ.เพชรบูรณ์')
@section('content')
<div class="container py-3" style="max-width: 860px;">
    <h3 class="fw-bold mb-3"><i class="bi bi-cookie text-warning me-2"></i>นโยบายคุกกี้ (Cookie Policy)</h3>
    <div class="card shadow-sm border"><div class="card-body lh-lg">
        <p>เว็บไซต์นี้ใช้คุกกี้ที่จำเป็นต่อการทำงานของระบบ เท่านั้น ได้แก่</p>
        <ul>
            <li><strong>คุกกี้เซสชัน/การยืนยันตัวตน</strong> เพื่อรักษาสถานะการเข้าสู่ระบบ</li>
            <li><strong>CSRF Token</strong> เพื่อป้องกันการส่งแบบฟอร์มโดยไม่ได้รับอนุญาต</li>
            <li><strong>การตั้งค่าธีม</strong> (สว่าง/มืด) ที่เก็บไว้ในเบราว์เซอร์ของท่าน</li>
        </ul>
        <p>ท่านสามารถปิดหรือลบคุกกี้ได้ที่การตั้งค่าเบราว์เซอร์ แต่บางฟังก์ชัน เช่น การเข้าสู่ระบบ อาจใช้งานไม่ได้</p>
    </div></div>
</div>
@endsection
