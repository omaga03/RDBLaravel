{{--
    Scholarly / SEO meta tags: Google Scholar (citation_*), Dublin Core (DC.*), OpenGraph (og:*), Twitter Cards, and Schema.org JSON-LD
    Usage: @include('layouts.partials.scholar-meta', ['type' => 'project|published|dip|utilize|researcher|news|conference', 'item' => $item])
--}}
@php
    $clean = fn($v, $len = 300) => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8')))), $len, '');
    $siteName = 'ระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB)';
    $title = $desc = $keywords = $date = $journal = $pdfUrl = $image = null;
    $authors = [];
    $docType = 'Text';
    $jsonLd = null;

    switch ($type) {
        case 'project':
            $title = $item->pro_nameTH ?: $item->pro_nameEN;
            $desc = $item->pro_abstract;
            $keywords = $item->pro_keyword;
            $date = $item->pro_finish ?: $item->pro_date_end;
            $docType = 'Technical Report';
            foreach (($item->rdbProjectWorks ?? collect()) as $w) {
                if ($w->researcher) { 
                    $authors[] = trim(($w->researcher->prefix->prefix_nameTH ?? '').$w->researcher->researcher_fname.' '.$w->researcher->researcher_lname); 
                }
            }
            if (!empty($item->pro_file) || !empty($item->pro_file_path)) {
                $pdfUrl = route('frontend.rdbproject.view_report', $item->pro_id);
            }
            if (!empty($item->cover_url)) {
                $image = url($item->cover_url);
            }
            break;

        case 'published':
            $title = $item->pub_name;
            $desc = $item->pub_abstract;
            $keywords = $item->pub_keyword;
            $date = $item->pub_date;
            $journal = $item->pub_name_journal;
            $docType = 'Journal Article';
            foreach (($item->authors ?? collect()) as $a) { 
                $authors[] = trim($a->researcher_fname.' '.$a->researcher_lname); 
            }
            if (empty($authors) && $item->researcher) { 
                $authors[] = trim(($item->researcher->prefix->prefix_nameTH ?? '').$item->researcher->researcher_fname.' '.$item->researcher->researcher_lname); 
            }
            if (!empty($item->pub_file) || !empty($item->pub_file_path)) {
                $pdfUrl = route('frontend.rdbpublished.view_file', $item->id);
            }
            if (!empty($item->cover_url)) {
                $image = url($item->cover_url);
            }
            break;

        case 'researcher':
            $title = $item->full_name ?? ($item->researcher_fname.' '.$item->researcher_lname);
            $deptName = $item->department->department_nameTH ?? 'มหาวิทยาลัยราชภัฏเพชรบูรณ์';
            $desc = 'ข้อมูลประวัตินักวิจัย ผลงานวิจัย และผลงานตีพิมพ์ทางวิชาการของ ' . $title . ' สังกัด ' . $deptName;
            $docType = 'Person';
            if (!empty($item->profile_image_url)) {
                $image = url($item->profile_image_url);
            }
            break;

        case 'dip':
            $title = $item->dip_name ?: $item->dip_data2_name;
            $desc = $item->dip_detail ?? null;
            $date = $item->dip_request_date;
            $docType = 'Patent';
            if ($item->researcher) { 
                $authors[] = trim(($item->researcher->prefix->prefix_nameTH ?? '').$item->researcher->researcher_fname.' '.$item->researcher->researcher_lname); 
            }
            break;

        case 'utilize':
            $title = $item->utz_department_name;
            $desc = $item->utz_detail;
            $date = $item->utz_date;
            $docType = 'CreativeWork';
            break;

        case 'news':
            $title = $item->news_name;
            $desc = $item->news_detail;
            $date = $item->news_date;
            $image = !empty($item->image_url) ? url($item->image_url) : null;
            $docType = 'NewsArticle';
            break;

        case 'conference':
            $title = $item->con_name;
            $desc = $item->con_detail;
            $date = $item->con_even_date;
            $image = !empty($item->image_url) ? url($item->image_url) : null;
            $docType = 'Event';
            break;
    }

    $title = $clean($title, 250);
    $desc = $clean($desc, 300);
    $year = null;
    $dateIso = null;
    $dateIso8601 = null;
    if ($date) {
        try {
            $parsed = \Illuminate\Support\Carbon::parse($date);
            $year = $parsed->format('Y');
            $dateIso = $parsed->format('Y/m/d');
            $dateIso8601 = $parsed->format('Y-m-d');
        } catch (\Throwable $e) {
            // Thai text date fallback
        }
    }
    $kwList = $keywords ? array_filter(array_map('trim', preg_split('/[,;]/u', $clean($keywords, 500)))) : [];

    // Build Schema.org Structured Data (JSON-LD)
    $orgSchema = [
        '@type' => 'EducationalOrganization',
        'name' => 'มหาวิทยาลัยราชภัฏเพชรบูรณ์',
        'url' => url('/'),
        'logo' => asset('favicon.ico'),
    ];

    switch ($type) {
        case 'published':
            $authorObjects = array_map(fn($name) => [
                '@type' => 'Person',
                'name' => $name,
            ], $authors);

            $jsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'ScholarlyArticle',
                'headline' => $title,
                'name' => $title,
                'abstract' => $desc ?: $title,
                'url' => url()->current(),
                'mainEntityOfPage' => url()->current(),
                'publisher' => $orgSchema,
            ];
            if ($authorObjects) {
                $jsonLd['author'] = $authorObjects;
            }
            if ($dateIso8601) {
                $jsonLd['datePublished'] = $dateIso8601;
            }
            if ($journal) {
                $jsonLd['isPartOf'] = [
                    '@type' => 'Periodical',
                    'name' => $clean($journal, 200),
                ];
            }
            if ($image) {
                $jsonLd['image'] = $image;
            }
            if ($pdfUrl) {
                $jsonLd['encoding'] = [
                    '@type' => 'MediaObject',
                    'contentUrl' => $pdfUrl,
                    'encodingFormat' => 'application/pdf',
                ];
            }
            if ($kwList) {
                $jsonLd['keywords'] = implode(', ', $kwList);
            }
            break;

        case 'project':
            $jsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'ResearchProject',
                'name' => $title,
                'description' => $desc ?: $title,
                'url' => url()->current(),
                'funder' => [
                    '@type' => 'Organization',
                    'name' => $item->fund->fund_name ?? 'มหาวิทยาลัยราชภัฏเพชรบูรณ์',
                ],
                'parentOrganization' => $orgSchema,
            ];
            if (!empty($authors[0])) {
                $jsonLd['principalInvestigator'] = [
                    '@type' => 'Person',
                    'name' => $authors[0],
                ];
            }
            if ($dateIso8601) {
                $jsonLd['startDate'] = $dateIso8601;
            }
            if ($image) {
                $jsonLd['image'] = $image;
            }
            break;

        case 'researcher':
            $sameAs = [];
            if (!empty($item->orcid)) {
                $sameAs[] = 'https://orcid.org/' . trim($item->orcid);
            }
            if (!empty($item->scopus_authorId)) {
                $sameAs[] = 'https://www.scopus.com/authid/detail.uri?authorId=' . trim($item->scopus_authorId);
            }

            $jsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $title,
                'jobTitle' => $item->position_academic ?? 'อาจารย์ / นักวิจัย',
                'worksFor' => $orgSchema,
                'url' => url()->current(),
            ];
            if ($image) {
                $jsonLd['image'] = $image;
            }
            if ($sameAs) {
                $jsonLd['sameAs'] = $sameAs;
            }
            break;

        default:
            $jsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'description' => $desc ?: $title,
                'url' => url()->current(),
                'publisher' => $orgSchema,
            ];
            if ($image) {
                $jsonLd['image'] = $image;
            }
            break;
    }
@endphp
@push('meta')
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="description" content="{{ $desc ?: $title }}">
    <meta property="og:type" content="{{ in_array($type, ['published','project']) ? 'article' : ($type === 'researcher' ? 'profile' : 'website') }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $desc ?: $title }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="th_TH">
    @if($image)
        <meta property="og:image" content="{{ $image }}">
        <meta property="og:image:alt" content="{{ $title }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $image }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $desc ?: $title }}">

    {{-- Dublin Core Metadata Standards --}}
    <meta name="DC.title" content="{{ $title }}">
    <meta name="DC.type" content="{{ $docType }}">
    <meta name="DC.publisher" content="มหาวิทยาลัยราชภัฏเพชรบูรณ์">
    <meta name="DC.language" content="th">
    <meta name="DC.identifier" content="{{ url()->current() }}">
    @if($desc)<meta name="DC.description" content="{{ $desc }}">@endif
    @if($dateIso8601)<meta name="DC.date" scheme="WTN8601" content="{{ $dateIso8601 }}">@endif
    @foreach($authors as $au)<meta name="DC.creator" content="{{ $au }}">@endforeach
    @foreach($kwList as $kw)<meta name="DC.subject" content="{{ $kw }}">@endforeach

    {{-- Google Scholar & Academic Harvester Highwire Press Tags --}}
    @if(in_array($type, ['project', 'published']))
        <meta name="citation_title" content="{{ $title }}">
        @foreach($authors as $au)
            <meta name="citation_author" content="{{ $au }}">
        @endforeach
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

    {{-- Schema.org Structured Data (JSON-LD) --}}
    @if($jsonLd)
        <script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif
@endpush
