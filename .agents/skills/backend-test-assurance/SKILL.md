---
name: backend-test-assurance
description: Test-driven procedures, automated regression testing checklist, and test suite execution using BackendAuditTest.
---

# Automated Test Assurance & Verification (laravel_rdb)

Use this skill whenever modifying backend controllers, Blade views, routes, models, or security mechanisms to prevent regressions.

## 1. Running the Automated Test Suite
Always execute the comprehensive audit test suite before committing code:
```bash
php artisan test --filter=BackendAuditTest
```

## 2. Test Coverage Checklist
Whenever a new feature or fix is introduced, verify and write tests for:
1. **Authorization Gate**: Guest or unauthorized role receives HTTP 403 or redirect.
2. **Success Case**: Admin receives HTTP 200 with expected UI elements and data.
3. **Validation Failure**: Submitting invalid data redirects with errors in session.
4. **Multi-Select Input (`old()`)**: Ensure arrays flashed in `old(...)` do not cause `TypeError htmlspecialchars`.
5. **Download / Inline Streaming**: Assert Content-Disposition header contains `inline` and correct filename.

## 3. Template for Testing Authenticated Routes
```php
public function test_example_feature()
{
    $admin = \App\Models\User::find(129); // Admin user
    $this->assertNotNull($admin);

    $response = $this->actingAs($admin)->get(route('backend.rdb_project.index'));
    $response->assertStatus(200);
    $response->assertSee('โครงการวิจัย');
}
```

## 4. Cache Clearing Protocol
After tests pass, clear compiled views and route caches:
```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
```
