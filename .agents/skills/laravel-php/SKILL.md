---
name: laravel-php
description: Best practices, coding conventions, and architectural rules for Laravel 12 and PHP 8 development in this project.
---

# Laravel PHP Development Skill

## Guidelines & Architecture
- ใช้ PHP 8.x และ Laravel 12 Conventions
- ยึดหลัก PSR-12 และ Clean Architecture
- ห้ามเขียน Business Logic ไว้ใน Controller ให้ย้ายไปใส่ Service Class เสมอ
- เขียน PHPUnit Test (Feature/Unit Test) ควบคู่ทุกครั้งที่สร้าง Feature ใหม่
- ตรวจสอบความปลอดภัย: Gate/Policy Authorization, UUID Filename obfuscation, ป้องกัน SQL/CSV Injection
