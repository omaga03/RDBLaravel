{{--
    Scholarly / SEO meta tags: Google Scholar (citation_*), Dublin Core (DC.*), OpenGraph (og:*)
    Usage: @include('layouts.partials.scholar-meta', ['type' => 'project|published|dip|utilize|news|conference', 'item' => $item])
--}}
@php
    $clean = fn($v, $len = 300) => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8')))), $len, '');
    $siteName = 'ระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์';
    $title = $desc = $keywords = $date = $journal = $pdfUrl = $image = null;
    $authors = [];
    $docType = 'Text';

    switch ($type) {
        case 'project':
            $title = $item->pro_nameTH ?: $item->pro_nameEN;
            $desc = $item->pro_abstract;
            $keywords = $item->pro_keyword;
            $date = $item->pro_finish ?: $item->pro_date_end;
            $docType = 'Technical Report';
            foreach (($item->rdbProjectWorks ?? collect()) as $w) {
                if ($w->researcher) { $authors[] = trim($w->researcher->researcher_fname.' '.$w->researcher->researcher_lname); }
            }
            if (!empty($item->pro_file) || !empty($item->pro_file_path)) {
                if ((int) $item->pro_file_show === 0) { $pdfUrl = route('frontend.rdbproject.view_report', $item->pro_id); }
            }
            break;
        case 'published':
            $title = $item->pub_name;
            $desc = $item->pub_abstract;
            $keywords = $item->pub_keyword;
            $date = $item->pub_date;
            $journal = $item->pub_name_journal;
            $docType = 'Journal Article';
            foreach (($item->authors ?? collect()) as $a) { $authors[] = trim($a->researcher_fname.' '.$a->researcher_lname); }
            if (empty($authors) && $item->researcher) { $authors[] = trim($item->researcher->researcher_fname.' '.$item->researcher->researcher_lname); }
            break;
        case 'dip':
            $title = $item->dip_name ?: $item->dip_data2_name;
            $desc = $item->dip_detail ?? null;
            $date = $item->dip_request_date;
            $docType = 'Patent';
            if ($item->researcher) { $authors[] = trim($item->researcher->researcher_fname.' '.$item->researcher->researcher_lname); }
            break;
        case 'utilize':
            $title = $item->utz_department_name;
            $desc = $item->utz_detail;
            $date = $item->utz_date;
            break;
        case 'news':
            $title = $item->news_name;
            $desc = $item->news_detail;
            $date = $item->news_date;
            $image = $item->image_url ?? null;
            break;
        case 'conference':
            $title = $item->con_name;
            $desc = $item->con_detail;
            $date = $item->con_even_date;
            $image = $item->image_url ?? null;
            break;
    }

    $title = $clean($title, 250);
    $desc = $clean($desc, 300);
    $year = null;
    $dateIso = null;
    if ($date) {
        try {
            $parsed = \Illuminate\Support\Carbon::parse($date);
            $year = $parsed->format('Y');
            $dateIso = $parsed->format('Y/m/d');
        } catch (\Throwable $e) {
            // Date stored in Thai format (e.g. "6 ตุลาคม 2563") — skip meta date
        }
    }
    $kwList = $keywords ? array_filter(array_map('trim', preg_split('/[,;]/u', $clean($keywords, 500)))) : [];
@endphp
@push('meta')
    <meta name="description" content="{{ $desc ?: $title }}">
    <meta property="og:type" content="{{ in_array($type, ['published','project']) ? 'article' : 'website' }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $desc ?: $title }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="th_TH">
    @if($image)<meta property="og:image" content="{{ $image }}">@endif
    <meta name="DC.title" content="{{ $title }}">
    <meta name="DC.type" content="{{ $docType }}">
    <meta name="DC.publisher" content="มหาวิทยาลัยราชภัฏเพชรบูรณ์">
    <meta name="DC.language" content="th">
    <meta name="DC.identifier" content="{{ url()->current() }}">
    @if($desc)<meta name="DC.description" content="{{ $desc }}">@endif
    @if($dateIso)<meta name="DC.date" content="{{ $dateIso }}">@endif
    @foreach($authors as $au)<meta name="DC.creator" content="{{ $au }}">@endforeach
    @foreach($kwList as $kw)<meta name="DC.subject" content="{{ $kw }}">@endforeach
    @if(in_array($type, ['project', 'published']))
    <meta name="citation_title" content="{{ $title }}">
    @foreach($authors as $au)<meta name="citation_author" content="{{ $au }}">@endforeach
    @if($dateIso)<meta name="citation_publication_date" content="{{ $dateIso }}">@endif
    @if($year)<meta name="citation_year" content="{{ $year }}">@endif
    <meta name="citation_publisher" content="มหาวิทยาลัยราชภัฏเพชรบูรณ์">
    <meta name="citation_language" content="th">
    @if($journal)<meta name="citation_journal_title" content="{{ $clean($journal, 250) }}">@endif
    @if($type === 'project')<meta name="citation_technical_report_institution" content="มหาวิทยาลัยราชภัฏเพชรบูรณ์">@endif
    @if($kwList)<meta name="citation_keywords" content="{{ implode('; ', $kwList) }}">@endif
    <meta name="citation_abstract_html_url" content="{{ url()->current() }}">
    @if($pdfUrl)<meta name="citation_pdf_url" content="{{ $pdfUrl }}">@endif
    @endif
@endpush
