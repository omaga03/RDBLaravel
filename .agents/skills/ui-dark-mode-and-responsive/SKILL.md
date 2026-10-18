---
name: ui-dark-mode-and-responsive
description: Rules and CSS standards for Bootstrap 5.3 dark and light modes, responsive layouts, and mobile-friendly navigation.
---

# Dark Mode & Responsive Layout Standards (laravel_rdb)

Use this skill whenever styling pages, adding CSS, or structuring layouts to ensure flawless readability across Dark and Light modes as well as mobile devices.

## 1. Avoid Hardcoded Colors (Use Bootstrap Semantic Tokens)
The application dynamically toggles `data-bs-theme="dark"` and `data-bs-theme="light"`.
- **NEVER** write inline styles or CSS with hardcoded black/white colors:
  ❌ `color: #000; background: #fff;` (Becomes unreadable in dark mode!)
  ❌ `border: 1px solid #dee2e6;` (Looks harsh in dark mode!)
- **ALWAYS** use Bootstrap 5.3 semantic color utilities:
  ✅ **Text**: `text-body`, `text-body-secondary`, `text-body-emphasis`
  ✅ **Backgrounds**: `bg-body`, `bg-body-secondary`, `bg-body-tertiary`
  ✅ **Borders**: `border border-body-secondary` or `var(--bs-border-color)`

## 2. Table Readability in Dark Mode
All data tables must follow this structure:
```blade
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-4">...</th>
            </tr>
        </thead>
        <tbody>
            <!-- rows -->
        </tbody>
    </table>
</div>
```
Bootstrap automatically adjusts `.table-light` in dark mode to an appropriate elevated dark tone.

## 3. Responsive Navigation
- **Desktop (>= 992px)**: Sidebar navigation via `resources/views/layouts/partials/sidebar.blade.php`.
- **Mobile (< 992px)**: Bottom Navigation Bar via `resources/views/layouts/partials/bottom_nav.blade.php`.
- When adding new primary menu items, ensure they are registered in both sidebar and bottom navigation partials.
- Active link check:
  `{{ request()->routeIs('backend.module.*') ? 'active fw-bold' : '' }}`
