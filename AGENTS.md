# Project Development Guidelines & Rules

## Laravel & PHP Standards
- ใช้ PHP 8.x และ Laravel 12 Conventions เสมอ
- ยึดหลัก PSR-12 และ Clean Architecture
- ห้ามเขียน Business Logic ไว้ใน Controller ให้ย้ายไปใส่ Service Class เสมอ
- เขียน PHPUnit Test (Feature/Unit Test) ควบคู่ทุกครั้งที่มีการสร้างหรือแก้ไข Feature ใหม่
- คำนึงถึงความปลอดภัย (Authorization Gates, File Upload UUID Renaming, Security Headers, Rate Limiting)

## File Storage & Legacy Fallback Standards
- จัดเก็บไฟล์ใหม่แยกตามโฟลเดอร์โมดูลใน `storage/app/public/{module}/` (UUID ปลอดภัย)
- มีระบบ Smart Multi-Fallback ค้นหาไฟล์เก่าอัตโนมัติ 3 ลำดับชั้น (ไฟล์ใหม่ -> โฟลเดอร์ `uploads/` -> โฟลเดอร์เดิม Yii2)
- การดาวน์โหลดและเปิดดูไฟล์ทุกโมดูลต้องวิ่งผ่าน Controller Route เสมอ (ห้ามฮาร์ดโค้ด `asset('storage/...')` ใน Blade)
- หากไม่พบไฟล์บนเซิร์ฟเวอร์ ต้องแสดงผลผ่าน Custom 404 Template ที่เป็นมิตร
- รายละเอียดข้อกำหนดและตาราง Mapping ทั้งหมดถูกบันทึกไว้ใน `.agents/skills/legacy-data-migration/SKILL.md`
