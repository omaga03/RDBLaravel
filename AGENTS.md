# Project Development Guidelines & Rules

## Laravel & PHP Standards
- ใช้ PHP 8.x และ Laravel 12 Conventions เสมอ
- ยึดหลัก PSR-12 และ Clean Architecture
- ห้ามเขียน Business Logic ไว้ใน Controller ให้ย้ายไปใส่ Service Class เสมอ
- เขียน PHPUnit Test (Feature/Unit Test) ควบคู่ทุกครั้งที่มีการสร้างหรือแก้ไข Feature ใหม่
- คำนึงถึงความปลอดภัย (Authorization Gates, File Upload UUID Renaming, Security Headers, Rate Limiting)
