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

## 4. Aesthetic Anchors, Token Locking & Anti-AI-Slop Craftsmanship (Ilm-Alan Frontend Design Protocol)
To ensure the application feels genuinely crafted by a senior human designer for an academic institution—and never resembles generic "AI-slop"—strictly follow these aesthetic discipline rules:

### 4.1 Aesthetic Anchor: Scholarly Editorial & Swiss Modernism
- **Visual Direction**: Inspire confidence through crisp editorial precision (inspired by Stripe Docs, Nature Portfolio, and Linear). Prioritize high-contrast typography, structured hairline borders (`1px solid`), and calm, deliberate surfaces over flashy decoration.
- **No Generic AI Gradients or Neon Glows**:
  - ❌ **FORBIDDEN**: Purple-to-pink/cyan neon gradients (`#8b5cf6` to `#ec4899`), glowing box-shadows (`0 0 20px rgba(...)`), or heavy glassmorphism everywhere.
  - ✅ **REQUIRED**: Solid, grounded academic surfaces (`--bs-card-bg`) paired with subtle top/left accent borders or refined Deep Navy (`#0F2744` -> `#1E3A5F`) and Citation Gold (`#D97706`) accents.

### 4.2 Token Locking & Atmospheric Polarity (Light vs. Dark Mode)
- **Light Mode (Daylight Editorial Slate)**:
  - Warm/clean paper-slate canvas (`#F8FAFC`), crisp white elevated surfaces (`#FFFFFF`), and subtle structural hairline borders (`#E2E8F0` / `rgba(15, 23, 42, 0.08)`).
  - Table headers and filter headers should use clean, structured surfaces or deep scholarly navy with crisp `1px` borders—never muddy or overly saturated blocks.
- **Dark Mode (Carbon-Navy Night Reading)**:
  - Deep carbon-navy canvas (`#090F1E` / `#0B1329`) with layered surface elevation (`#111C35` for cards, `#162444` for inputs/dropdowns) and subtle specular top-edge borders (`#1E2E4E` / `rgba(255, 255, 255, 0.07)`).
  - Never use pure `#000000` backgrounds or glaring `#FFFFFF` text for long paragraphs; use `#F1F5F9` for primary headings and `#CBD5E1` / `#94A3B8` for comfortable long-form reading.

### 4.3 Tabular Numerals & Micro-Interactions
- **Tabular Numerals (`font-variant-numeric: tabular-nums`)**:
  - Always apply `.tabular-nums` or `font-variant-numeric: tabular-nums` to budgets (`pro_budget`), years (`2569`), project codes, citation counts, percentages, and date columns so digits align vertically with Swiss grid precision.
- **Tactile Micro-Interactions**:
  - Avoid exaggerated card bouncing (`translateY(-5px)`). Use subtle, responsive transitions (`150ms cubic-bezier(0.22, 1, 0.36, 1)`), crisp focus rings (`0 0 0 3px rgba(37, 99, 235, 0.16)`), and clear active states.
  - Never hardcode `.text-dark` inside components (such as navbar dropdowns or notification lists) where dark mode is active; always use `.text-body` or `.text-body-emphasis`.
