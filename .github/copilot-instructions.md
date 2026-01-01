# RDBLaravel Copilot Instructions

## Project Overview
RDBLaravel (Research Database Management System) is a Laravel 12 application for managing research project data in Thai academic institutions. It's a complex multi-module CRUD system with role-based access control, hierarchical relationships, and extensive data management capabilities.

## Architecture & Key Components

### Database & Models (app/Models/)
- **60+ Eloquent Models** prefixed with `Rdb*` (e.g., `RdbProject`, `RdbPublished`, `RdbResearcher`)
- **Key entities**: Projects, Publications, Researchers, Departments, Years, Strategic goals, Budgets, Utilization tracking
- **Global Scope Pattern**: Models use `HasDataShowScope` trait to auto-filter with `where('data_show', 1)` globally
- **Database**: MySQL/MariaDB configured in `config/database.php` with Thai locale; charset: `utf8mb4`
- **No timestamps**: Most models set `public $timestamps = false`; created_at/updated_at manually managed

### Controller Architecture
- **Dual Controller Structure**: `/Backend/` for admin operations and `/Frontend/` for public views
- **Example**: `RdbProjectController` (Backend) has 1139 lines with complex search/filter logic across 10+ fields
- **Smart Search**: Controllers implement multi-word search splitting by space/slash/comma with OR/AND logic across relationships
- **Custom Requests**: Form validation in `app/Http/Requests/Backend/` and `Frontend/`

### Frontend Stack
- **Vite + Tailwind CSS**: Asset pipeline with hot-reload (see `vite.config.js`)
- **Blade Templates**: Resources in `resources/views/`
- **Alpine.js**: Client-side interactivity via `package.json` dependency
- **Build commands**: `npm run dev` (development watch), `npm run build` (production)

### RBAC System (Role-Based Access Control)
- **Service**: `App\Services\RbacService` handles hierarchical permission checks
- **Models**: `AuthAssignment`, `AuthItem`, `AuthItemChild` manage roles/permissions
- **Pattern**: Roles assigned to users; permissions organized hierarchically; recursive permission checking
- **Usage**: Controllers use `Gate::authorize()` for policy enforcement

## Critical Developer Workflows

### Local Development Setup
```bash
# Full setup with all dependencies
composer run-script setup

# Or individually:
composer install
npm install && npm run build
php artisan migrate --force
php artisan serve              # Starts Laravel server on localhost:8000
```

### Development Server (Concurrent Processes)
```bash
composer run dev
# Runs: Laravel server + queue listener + log tail + Vite dev server concurrently
```

### Testing & Code Quality
```bash
composer test                  # Run PHPUnit test suite
php artisan pint               # Fix code style (Laravel Pint)
```

### Database & Migrations
- Migrations in `database/migrations/` with sequential timestamp naming
- **Schema notes**: Thai-heavy naming; foreign keys (e.g., `depcat_id` → `RdbDepartmentCategory`)
- **Recent migrations**: Department category additions, UTF-8 cleanup, h-index fields (2025-12)
- **Backups**: Ordered SQL backups in `backups/` folder (data + structure chunks)

## Project-Specific Patterns

### Data Visibility Control
- **Pattern**: `data_show` column (0/1 binary) in all models
- **Usage**: Global scope in `HasDataShowScope` trait automatically filters inactive records
- **Important**: When querying, remember this filter is applied automatically

### Thai Language Localization
- **Default locale**: `'th'` in `config/app.php`
- **Translation files**: `lang/th.json` for Thai translations
- **Model fields**: `*_nameTH` and `*_nameEN` naming convention (e.g., `pro_nameTH`, `pro_nameEN`)
- **Database charset**: Configured as `utf8mb4` to support Thai characters

### Complex Search Implementation
- **Multi-word queries**: Split by whitespace/slash/comma with separate OR clauses per word
- **Relationship searching**: Search spans related models via `whereHas()` (departments, researchers, types)
- **Example in RdbProjectController**: 90+ lines of search logic handling 10+ fields and relationships

### File Handling
- **Storage**: Laravel's `Storage` facade; see `app/Http/Controllers/Backend/RdbProjectController` line ~20
- **File uploads**: Managed via `RdbProjectFiles` model and upload endpoints

## Key Dependencies & Services

### Composer Packages
- **laravel/framework**: ^12.0
- **laravel/breeze**: Authentication scaffolding
- **reliese/laravel**: Model/migration auto-generation from existing DB
- **phpunit/phpunit**: ^11.5.3 for testing

### NPM Packages  
- **@tailwindcss/forms**: Form styling
- **laravel-vite-plugin**: Asset bundling
- **alpinejs**: ^3.4.2 for interactive components
- **axios**: AJAX requests

### Environment Configuration
- `.env.example` template required; `.env` is generated during setup
- **Key vars**: `APP_NAME`, `APP_KEY`, `DB_CONNECTION`, `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`
- **App locale**: Default is Thai (`APP_LOCALE=th`)

## Common Conventions & Gotchas

1. **Model Primary Keys**: Often custom (e.g., `pro_id`, `ps_id`), not always `id`
   - Check `protected $primaryKey` in model definitions

2. **Relationships**: Browse `app/Models/Rdb*.php` to understand foreign key mappings
   - Example: `RdbProject::belongsTo(RdbProjectStatus::class, 'ps_id', 'ps_id')`

3. **Form Validation**: Separate request classes per form; see `app/Http/Requests/Backend/` and `Frontend/`

4. **Asset Pipeline**: Always run `npm run build` after CSS/JS changes for production
   - Vite handles hot-reload in development

5. **Queue System**: Configured in `config/queue.php`; listen via `php artisan queue:listen`

6. **Caching**: `RbacService` uses `Cache::remember()` for permission lookups; invalidate carefully

## File Structure Reference
- **Routes**: `routes/web.php` (~250 lines; separate middleware groups for auth/admin)
- **Main Config**: `config/app.php` (locale, timezone), `config/database.php` (charset/collation)
- **Commands**: `app/Console/Commands/`
- **Helpers**: `app/Helpers/`
- **Database Utils**: Debug scripts like `check_codeid_data.php`, `check_column_details.php`

## When Debugging Issues
- Check `.env` locale setting matches expected language output
- Verify `data_show=1` for records expected to appear (global scope issue)
- Review `RbacService::checkPermission()` if permission checks fail
- Inspect controller search logic for multi-word query edge cases
- Use Laravel Pail logs: `php artisan pail` from `composer dev`
