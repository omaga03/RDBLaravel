<?php

require 'C:/laragon/www/laravel_rdb/vendor/autoload.php';

use Illuminate\Support\Str;

$manualDir = 'C:\laragon\www\laravel_rdb\docs\manuals';

$manuals = [
    'VOLUME_1_FRONTEND_USER_MANUAL' => [
        'title' => 'เล่มที่ 1: คู่มือการใช้งานระบบหน้าบ้าน (Volume 1: Frontend Public User Manual)',
        'subtitle' => 'สำหรับประชาชนทั่วไป นักศึกษา คณาจารย์ และหน่วยงานภายนอก',
        'badge' => 'VOLUME 1 — PUBLIC PORTAL',
    ],
    'VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL' => [
        'title' => 'เล่มที่ 2: คู่มือการใช้งานระบบหลังบ้าน (Volume 2: Backend Admin & Researcher Manual)',
        'subtitle' => 'สำหรับนักวิจัย ผู้ทรงคุณวุฒิ ผู้ประสานงานคณะ และผู้ดูแลระบบ',
        'badge' => 'VOLUME 2 — BACKEND & WORKFLOW',
    ],
    'RESEARCHER_QUICK_START_CHEATSHEET' => [
        'title' => 'คู่มือฉบับย่อสำหรับนักวิจัย: การส่งผลงานและขออนุมัติเผยแพร่ใน 3 นาที',
        'subtitle' => 'Researcher Quick-Start Cheatsheet (PCRU Research Database)',
        'badge' => 'QUICK-START CHEATSHEET',
    ],
    'SYSTEM_ACCEPTANCE_TEST_PLAN' => [
        'title' => 'แผนการทดสอบระบบและบันทึกผลการทดสอบจริง (Production Acceptance Test Plan)',
        'subtitle' => 'รายงานผลการตรวจรับระบบ (UAT & Automated Verification Report — 124 Passed / 1,884 Assertions)',
        'badge' => 'UAT & VERIFICATION REPORT',
    ],
    'USER_MANUAL_DEVELOPMENT_PLAN' => [
        'title' => 'แผนแม่บทการจัดทำเล่มคู่มือการใช้งานระบบฐานข้อมูลงานวิจัย',
        'subtitle' => 'System User Manual Master Plan (PCRU Research Database)',
        'badge' => 'DOCUMENTATION MASTER PLAN',
    ],
];

function manualSlug(string $text): string
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = mb_strtolower($text, 'UTF-8');
    // Keep Unicode letters (\p{L}), Unicode marks/combining vowels (\p{M}), numbers (\p{N}), spaces, and hyphens
    $text = preg_replace('/[^\p{L}\p{M}\p{N}\s\-]+/u', '', $text);
    // Replace whitespace with hyphens
    $text = str_replace(' ', '-', $text);
    $text = preg_replace('/\s+/u', '-', $text);
    return trim($text, '-');
}

function convertMarkdownToEditorialHtml(string $md): string
{
    // Normalize line endings
    $md = str_replace("\r\n", "\n", $md);

    // 1. Convert ````carousel ... ```` blocks into HTML figure grids
    $md = preg_replace_callback('/````carousel\n(.*?)\n````/s', function ($m) {
        $slides = preg_split('/\n<!--\s*slide\s*-->\n/', trim($m[1]));
        $html = '<div class="manual-carousel-grid">';
        foreach ($slides as $idx => $slide) {
            $slideHtml = Str::markdown(trim($slide));
            $num = $idx + 1;
            $html .= '<div class="manual-carousel-item"><div class="carousel-badge">ภาพชุดที่ ' . $num . '</div>' . $slideHtml . '</div>';
        }
        $html .= '</div>';
        return "\n\n" . $html . "\n\n";
    }, $md);

    // 2. Extract ```mermaid ... ``` blocks into placeholders so CommonMark won't parse 4-space indentation as <pre><code>
    $mermaidBlocks = [];
    $md = preg_replace_callback('/```mermaid\r?\n(.*?)\r?\n```/s', function ($m) use (&$mermaidBlocks) {
        $idx = count($mermaidBlocks);
        $mermaidBlocks[$idx] = trim($m[1]);
        return "\n\n<!--MERMAID_PLACEHOLDER_{$idx}-->\n\n";
    }, $md);

    // 3. Convert GitHub Alerts > [!NOTE], > [!TIP], > [!IMPORTANT], > [!WARNING]
    $md = preg_replace_callback('/^>\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*\n((?:>.*(?:\n|$))+)/m', function ($m) {
        $type = strtolower($m[1]);
        $lines = preg_replace('/^>\s?/m', '', trim($m[2]));
        $innerHtml = Str::markdown($lines);
        $labels = [
            'note' => ['icon' => 'bi-info-circle-fill', 'title' => 'ข้อมูลจำเพาะ / หมายเหตุ (NOTE)'],
            'tip' => ['icon' => 'bi-lightbulb-fill', 'title' => 'เคล็ดลับการใช้งาน (TIP)'],
            'important' => ['icon' => 'bi-exclamation-diamond-fill', 'title' => 'ข้อสำคัญ (IMPORTANT)'],
            'warning' => ['icon' => 'bi-exclamation-triangle-fill', 'title' => 'ข้อควรระวัง (WARNING)'],
            'caution' => ['icon' => 'bi-shield-exclamation', 'title' => 'คำเตือนความปลอดภัย (CAUTION)'],
        ];
        $cfg = $labels[$type] ?? $labels['note'];
        return "\n\n<div class=\"manual-callout callout-{$type}\"><div class=\"callout-header\"><i class=\"bi {$cfg['icon']}\"></i> {$cfg['title']}</div><div class=\"callout-body\">{$innerHtml}</div></div>\n\n";
    }, $md);

    // 4. Parse remaining Markdown with CommonMark
    $html = Str::markdown($md);

    // 5. Add id attributes to h1-h4 for TOC navigation and wrap images in figure captions
    $html = preg_replace_callback('/<h([1-4])>(.*?)<\/h\1>/s', function ($m) {
        $level = $m[1];
        $text = $m[2];
        $slug = manualSlug($text);
        $slugCollapsed = trim(preg_replace('/-+/', '-', $slug), '-');
        
        $anchors = array_unique(array_filter([
            $slug,
            $slugCollapsed,
            rawurlencode($slug),
            rawurlencode($slugCollapsed),
        ]));
        
        $primary = array_shift($anchors);
        $extraAnchorsHtml = '';
        foreach ($anchors as $alias) {
            $extraAnchorsHtml .= "<a id=\"{$alias}\" class=\"manual-anchor\"></a>";
        }
        return "{$extraAnchorsHtml}<h{$level} id=\"{$primary}\">{$m[2]}</h{$level}>";
    }, $html);

    $html = preg_replace_callback('/<p><img src="([^"]+)" alt="([^"]*)"\s*\/?><\/p>/', function ($m) {
        $src = $m[1];
        $alt = $m[2];
        return '<figure class="manual-figure"><a href="' . $src . '" target="_blank" title="คลิกเพื่อดูภาพขนาดเต็ม"><img src="' . $src . '" alt="' . htmlspecialchars($alt) . '" loading="lazy"></a><figcaption><i class="bi bi-camera me-1"></i> ' . htmlspecialchars($alt) . '</figcaption></figure>';
    }, $html);

    // 6. Restore Mermaid blocks from placeholders
    $html = preg_replace_callback('/(?:<p>\s*)?<!--MERMAID_PLACEHOLDER_(\d+)-->(?:\s*<\/p>)?/', function ($m) use ($mermaidBlocks) {
        $idx = (int)$m[1];
        $code = $mermaidBlocks[$idx] ?? '';
        return "\n<div class=\"mermaid-container\"><pre class=\"mermaid\">\n" . htmlspecialchars($code, ENT_NOQUOTES) . "\n</pre></div>\n";
    }, $html);

    return $html;
}

function wrapInEditorialTemplate(string $title, string $subtitle, string $badge, string $bodyHtml, string $currentKey): string
{
    $navLinks = [
        'index.html' => '<i class="bi bi-collection me-1"></i> สารบัญรวม',
        'VOLUME_1_FRONTEND_USER_MANUAL.html' => '<i class="bi bi-globe2 me-1"></i> เล่ม 1: หน้าบ้าน',
        'VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html' => '<i class="bi bi-shield-lock me-1"></i> เล่ม 2: หลังบ้าน',
        'RESEARCHER_QUICK_START_CHEATSHEET.html' => '<i class="bi bi-lightning-charge me-1"></i> คู่มือย่อ 3 นาที',
        'SYSTEM_ACCEPTANCE_TEST_PLAN.html' => '<i class="bi bi-check2-circle me-1"></i> รายงานผลทดสอบ UAT',
    ];

    $navHtml = '';
    foreach ($navLinks as $href => $label) {
        $active = ($href === $currentKey . '.html' || ($currentKey === 'index' && $href === 'index.html')) ? 'active' : '';
        $navHtml .= "<a href=\"{$href}\" class=\"nav-pill {$active}\">{$label}</a>";
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} — มหาวิทยาลัยราชภัฏเพชรบูรณ์</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Prompt:wght@400;500;600;700&family=Sarabun:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-body: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-subtle: #F1F5F9;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --navy: #1E3A5F;
            --royal: #2563EB;
            --amber: #D97706;
            --emerald: #059669;
            --danger: #DC2626;
        }
        [data-theme="dark"] {
            --bg-body: #0B1120;
            --bg-surface: #111827;
            --bg-subtle: #1E293B;
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted: #94A3B8;
            --border-color: #334155;
            --navy: #38BDF8;
            --royal: #60A5FA;
        }
        * { box-sizing: border-box; }
        html {
            scroll-behavior: smooth;
        }
        body {
            margin: 0;
            font-family: 'Sarabun', 'Plus Jakarta Sans', sans-serif;
            font-size: 16.5px;
            line-height: 1.75;
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }
        h1, h2, h3, h4, .font-heading {
            font-family: 'Prompt', 'Plus Jakarta Sans', sans-serif;
            line-height: 1.35;
            scroll-margin-top: 85px;
        }
        .manual-anchor {
            display: block;
            position: relative;
            visibility: hidden;
            height: 0;
            width: 0;
            scroll-margin-top: 85px;
        }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #1E3A5F !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #FFFFFF !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding: 0.65rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.15);
        }
        [data-theme="dark"] .topbar {
            background: #0F172A !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .topbar a,
        .topbar-brand {
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            color: #FFFFFF !important;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .topbar-brand i {
            color: #FBBF24;
            font-size: 1.15rem;
        }
        .topbar-brand:hover {
            color: #F8FAFC !important;
            opacity: 0.95;
        }
        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        .nav-pill {
            color: rgba(255, 255, 255, 0.88) !important;
            text-decoration: none;
            font-family: 'Prompt', sans-serif;
            font-size: 0.85rem;
            padding: 0.38rem 0.8rem;
            border-radius: 6px;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
        }
        .nav-pill:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #FFFFFF !important;
        }
        .nav-pill.active {
            background: #D97706 !important;
            color: #FFFFFF !important;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-action {
            background: rgba(255, 255, 255, 0.12) !important;
            color: #FFFFFF !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
        }
        .btn-action:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            color: #FFFFFF !important;
            border-color: rgba(255, 255, 255, 0.45) !important;
        }
        .container {
            max-width: 1120px;
            margin: 2rem auto 4rem;
            padding: 0 1.25rem;
        }
        .manual-sheet {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
            padding: 3rem 3.5rem;
        }
        .hero-banner {
            border-bottom: 3px solid var(--navy);
            padding-bottom: 1.75rem;
            margin-bottom: 2.25rem;
        }
        .hero-badge {
            display: inline-block;
            background: rgba(217, 119, 6, 0.14);
            color: #B45309;
            border: 1px solid rgba(217, 119, 6, 0.3);
            font-family: 'Prompt', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            margin-bottom: 0.75rem;
        }
        [data-theme="dark"] .hero-badge { color: #FBBF24; }
        .hero-banner h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            color: var(--text-primary);
        }
        .hero-banner p {
            margin: 0;
            font-size: 1.05rem;
            color: var(--text-secondary);
        }
        .manual-content h1 { display: none; }
        .manual-content h2 {
            font-size: 1.5rem;
            color: var(--navy);
            border-left: 5px solid var(--amber);
            padding-left: 0.85rem;
            margin-top: 2.75rem;
            margin-bottom: 1.1rem;
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
            background: var(--bg-subtle);
            border-radius: 0 8px 8px 0;
        }
        .manual-content h3 {
            font-size: 1.2rem;
            color: var(--text-primary);
            margin-top: 1.85rem;
            margin-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.4rem;
        }
        .manual-content p, .manual-content li {
            color: var(--text-primary);
        }
        .manual-content ul, .manual-content ol {
            padding-left: 1.5rem;
        }
        .manual-content li { margin-bottom: 0.45rem; }
        .manual-content a { color: var(--royal); text-decoration: none; }
        .manual-content a:hover { text-decoration: underline; }
        .manual-content code {
            font-family: Consolas, Monaco, monospace;
            font-size: 0.9em;
            background: var(--bg-subtle);
            color: #D97706;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }
        .manual-content pre:not(.mermaid) {
            background: #0F172A;
            color: #E2E8F0;
            padding: 1.1rem 1.25rem;
            border-radius: 8px;
            overflow-x: auto;
        }
        .manual-content pre:not(.mermaid) code {
            background: transparent;
            color: inherit;
            border: none;
            padding: 0;
        }
        .manual-content hr {
            border: none;
            border-top: 1px solid var(--border-color);
            margin: 2.5rem 0;
        }
        .manual-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.25rem 0 1.75rem;
            font-size: 0.95rem;
        }
        .manual-content th, .manual-content td {
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            text-align: left;
            vertical-align: top;
        }
        .manual-content th {
            background: var(--bg-subtle);
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            color: var(--text-primary);
        }
        .manual-content tr:nth-child(even) td {
            background: rgba(148, 163, 184, 0.04);
        }
        .manual-figure {
            margin: 1.5rem 0 2rem;
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.85rem;
            text-align: center;
        }
        .manual-figure img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: block;
            margin: 0 auto;
        }
        .manual-figure figcaption {
            margin-top: 0.65rem;
            font-family: 'Prompt', sans-serif;
            font-size: 0.88rem;
            color: var(--text-secondary);
        }
        .manual-carousel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
            gap: 1.25rem;
            margin: 1.5rem 0 2rem;
        }
        .manual-carousel-item {
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.85rem;
        }
        .manual-carousel-item .manual-figure {
            margin: 0;
            border: none;
            padding: 0;
            background: transparent;
        }
        .carousel-badge {
            display: inline-block;
            background: var(--navy);
            color: #fff;
            font-family: 'Prompt', sans-serif;
            font-size: 0.75rem;
            padding: 0.15rem 0.6rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        .manual-callout {
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin: 1.35rem 0;
            border-left: 4px solid var(--royal);
            background: var(--bg-subtle);
        }
        .callout-header {
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }
        .callout-body p:last-child, .callout-body ul:last-child { margin-bottom: 0; }
        .callout-note { border-left-color: #2563EB; background: rgba(37, 99, 235, 0.06); }
        .callout-note .callout-header { color: #1D4ED8; }
        .callout-tip { border-left-color: #059669; background: rgba(5, 150, 105, 0.06); }
        .callout-tip .callout-header { color: #047857; }
        .callout-important { border-left-color: #D97706; background: rgba(217, 119, 6, 0.08); }
        .callout-important .callout-header { color: #B45309; }
        .callout-warning, .callout-caution { border-left-color: #DC2626; background: rgba(220, 38, 38, 0.06); }
        .callout-warning .callout-header, .callout-caution .callout-header { color: #B91C1C; }
        [data-theme="dark"] .callout-note .callout-header { color: #60A5FA; }
        [data-theme="dark"] .callout-tip .callout-header { color: #34D399; }
        [data-theme="dark"] .callout-important .callout-header { color: #FBBF24; }
        [data-theme="dark"] .callout-warning .callout-header { color: #F87171; }
        .mermaid-container {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.25rem;
            margin: 1.5rem 0;
            text-align: center;
            overflow-x: auto;
        }
        footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.88rem;
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        @media (max-width: 768px) {
            .manual-sheet { padding: 1.5rem 1.25rem; }
            .manual-carousel-grid { grid-template-columns: 1fr; }
        }
        @media print {
            body { background: #fff; color: #000; font-size: 11pt; }
            .topbar { display: none !important; }
            .container { max-width: 100%; margin: 0; padding: 0; }
            .manual-sheet { border: none; box-shadow: none; padding: 0; }
            .manual-figure, .manual-callout, table, tr { page-break-inside: avoid; }
            h2, h3 { page-break-after: avoid; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a href="index.html" class="topbar-brand">
            <i class="bi bi-journal-bookmark-fill"></i>
            <span>PCRU Research Database Manuals</span>
        </a>
        <nav class="topbar-nav">
            {$navHtml}
        </nav>
        <div class="topbar-actions">
            <button type="button" class="btn-action" onclick="toggleTheme()">
                <i class="bi bi-moon-stars" id="theme-icon"></i> <span>สลับโหมดสี</span>
            </button>
            <button type="button" class="btn-action" onclick="window.print()">
                <i class="bi bi-printer"></i> <span>พิมพ์ / บันทึก PDF</span>
            </button>
        </div>
    </header>

    <main class="container">
        <article class="manual-sheet">
            <div class="hero-banner">
                <div class="hero-badge">{$badge}</div>
                <h1>{$title}</h1>
                <p>{$subtitle} — สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์</p>
            </div>
            <div class="manual-content">
                {$bodyHtml}
            </div>
            <footer>
                เอกสารคู่มือการใช้งานระบบฐานข้อมูลงานวิจัย (PCRU Research Database) • มหาวิทยาลัยราชภัฏเพชรบูรณ์
            </footer>
        </article>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({ startOnLoad: true, theme: 'neutral' });
        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'bi bi-sun';
            } else {
                icon.className = 'bi bi-moon-stars';
            }
        }
        function toggleTheme() {
            const html = document.documentElement;
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('pcru_manual_theme', next);
            updateThemeIcon(next);
        }
        (function() {
            const saved = localStorage.getItem('pcru_manual_theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
                updateThemeIcon(saved);
            }

            // Smooth scrolling on hash links with Unicode support
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const rawHash = this.getAttribute('href').slice(1);
                    if (!rawHash) return;
                    const decoded = decodeURIComponent(rawHash);
                    const targetEl = document.getElementById(decoded) || 
                                     document.getElementById(rawHash) || 
                                     document.getElementById(encodeURIComponent(decoded));
                    if (targetEl) {
                        e.preventDefault();
                        history.pushState(null, null, '#' + rawHash);
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        })();
    </script>
</body>
</html>
HTML;
}

foreach ($manuals as $key => $meta) {
    $mdFile = $manualDir . DIRECTORY_SEPARATOR . $key . '.md';
    $htmlFile = $manualDir . DIRECTORY_SEPARATOR . $key . '.html';
    $md = file_get_contents($mdFile);
    $bodyHtml = convertMarkdownToEditorialHtml($md);
    $fullHtml = wrapInEditorialTemplate($meta['title'], $meta['subtitle'], $meta['badge'], $bodyHtml, $key);
    file_put_contents($htmlFile, $fullHtml);
    echo "Generated {$key}.html (" . filesize($htmlFile) . " bytes)\n";
}

// Generate index.html portal page
$indexBody = <<<'HTML'
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin: 1rem 0 2rem;">
    <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 1.5rem; background: var(--bg-subtle); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <span class="hero-badge">VOLUME 1 • 8 บทเรียน</span>
            <h3 style="margin-top: 0.25rem; border: none; padding: 0;">เล่มที่ 1: คู่มือการใช้งานระบบหน้าบ้าน (Public Portal)</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">สำหรับประชาชนทั่วไป นักศึกษา และคณาจารย์ ครอบคลุมการสืบค้นอัจฉริยะ, โครงการวิจัย, สิทธิ์เอกสาร 3 ระดับ, ผลงานตีพิมพ์, การคัดลอก Citation (APA 7 / IEEE / MLA 9), ทรัพย์สินทางปัญญา, การใช้ประโยชน์, ทำเนียบนักวิจัย และแดชบอร์ดสถิติ</p>
        </div>
        <a href="VOLUME_1_FRONTEND_USER_MANUAL.html" style="display: inline-block; text-align: center; background: #1E3A5F; color: #fff; padding: 0.65rem 1rem; border-radius: 6px; font-family: 'Prompt', sans-serif; font-weight: 500; margin-top: 1rem;">เปิดอ่านเล่มที่ 1 <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 1.5rem; background: var(--bg-subtle); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <span class="hero-badge">VOLUME 2 • 11 บทเรียน</span>
            <h3 style="margin-top: 0.25rem; border: none; padding: 0;">เล่มที่ 2: คู่มือการใช้งานระบบหลังบ้าน (Admin & Researcher)</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">สำหรับนักวิจัย ผู้ทรงคุณวุฒิ และผู้ดูแลระบบ ครอบคลุม Google SSO, สิทธิ์ RBAC & Impersonation, วงจรการส่งร่างและอนุมัติผลงาน (WF-01 ถึง WF-07), ระบบตรวจนับหน้า PDF & ปก .webp, ระบบ Peer Review, ส่งออก Excel UTF-8 BOM, คลังปัญญา DSpace SAF, ศูนย์คำสั่งบำรุงรักษา, ระบบสำรองและกู้คืนฐานข้อมูลด้วย Email OTP (The Creator Only), การตั้งค่าระบบ Mail และการตั้งเวลาทำงานอัตโนมัติ Cron Tasks พร้อมแถบตรวจสุขภาพเซิร์ฟเวอร์แบบเรียลไทม์</p>
        </div>
        <a href="VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html" style="display: inline-block; text-align: center; background: #1E3A5F; color: #fff; padding: 0.65rem 1rem; border-radius: 6px; font-family: 'Prompt', sans-serif; font-weight: 500; margin-top: 1rem;">เปิดอ่านเล่มที่ 2 <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 1.5rem; background: var(--bg-subtle); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <span class="hero-badge">QUICK-START • 3 นาที</span>
            <h3 style="margin-top: 0.25rem; border: none; padding: 0;">คู่มือฉบับย่อสำหรับนักวิจัย (Quick-Start Cheatsheet)</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">สรุปขั้นตอนสำคัญสำหรับอาจารย์และนักวิจัยในหน้าเดียว: การล็อกอินด้วยอีเมลมหาวิทยาลัย, การสร้างฉบับร่าง, การกดส่งตรวจสอบ และการขอปลดล็อกเพื่อแก้ไขข้อมูล</p>
        </div>
        <a href="RESEARCHER_QUICK_START_CHEATSHEET.html" style="display: inline-block; text-align: center; background: #D97706; color: #fff; padding: 0.65rem 1rem; border-radius: 6px; font-family: 'Prompt', sans-serif; font-weight: 500; margin-top: 1rem;">เปิดอ่านคู่มือฉบับย่อ <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</div>
HTML;

$indexHtml = wrapInEditorialTemplate(
    'ศูนย์รวมคู่มือการใช้งานระบบฐานข้อมูลงานวิจัย (System User Manuals)',
    'เอกสารคู่มือฉบับสมบูรณ์พร้อมภาพประกอบจากระบบจริง สำหรับประชาชน นักวิจัย และผู้ดูแลระบบ',
    'OFFICIAL DOCUMENTATION PORTAL',
    $indexBody,
    'index'
);
file_put_contents($manualDir . DIRECTORY_SEPARATOR . 'index.html', $indexHtml);
echo "Generated index.html (" . filesize($manualDir . DIRECTORY_SEPARATOR . 'index.html') . " bytes)\n";
