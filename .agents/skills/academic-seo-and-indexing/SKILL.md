---
name: academic-seo-and-indexing
description: Standards and guidelines for Academic SEO, Google Scholar indexing (Highwire Press & Dublin Core), Schema.org JSON-LD structured data, Open Graph social previews, and dynamic XML sitemaps for the university research database.
---

# Academic SEO & Research Indexing Standards (laravel_rdb)

Use this skill whenever working on public-facing research pages, publications, researcher profiles, sitemaps, metadata headers, and search engine discoverability.

## 1. Google Scholar & Academic Harvester Indexing

Google Scholar, Microsoft Academic, and academic harvesters rely primarily on **Highwire Press** and **Dublin Core** tags in the `<head>` of individual record pages.

### Rule 1: One Tag per Author
Google Scholar strictly requires that **each author has their own individual tag**. Never comma-separate authors inside a single tag:
```blade
{{-- CORRECT --}}
@foreach($authors as $author)
    <meta name="citation_author" content="{{ $author->name }}">
@endforeach

{{-- WRONG: DO NOT DO THIS --}}
<meta name="citation_author" content="{{ $author1 }}, {{ $author2 }}">
```

### Rule 2: Highwire Press Citation Tags
In publication detail views (`rdbpublished/show.blade.php`), include:
- `<meta name="citation_title" content="...">`
- `<meta name="citation_author" content="...">` (one per author)
- `<meta name="citation_publication_date" content="YYYY/MM/DD">` (Gregorian CE format)
- `<meta name="citation_journal_title" content="...">`
- `<meta name="citation_volume" content="...">` (if available)
- `<meta name="citation_issue" content="...">` (if available)
- `<meta name="citation_firstpage" content="...">`
- `<meta name="citation_lastpage" content="...">`
- `<meta name="citation_doi" content="...">` (if available)
- `<meta name="citation_pdf_url" content="{{ $pdfDownloadUrl }}">` (Direct open link to full text PDF)
- `<meta name="citation_abstract_html_url" content="{{ url()->current() }}">`

### Rule 3: Dublin Core Tags
Accompany Highwire Press with standard Dublin Core tags:
- `<meta name="DC.title" content="...">`
- `<meta name="DC.creator" content="...">`
- `<meta name="DC.date" scheme="WTN8601" content="YYYY-MM-DD">`
- `<meta name="DC.publisher" content="มหาวิทยาลัยราชภัฏเพชรบูรณ์">`
- `<meta name="DC.description" content="...">` (abstract / summary)
- `<meta name="DC.identifier" content="{{ url()->current() }}">`

---

## 2. Schema.org JSON-LD Structured Data

Every show page must inject structured data via `@push('meta')` in `<script type="application/ld+json">`.

### ScholarlyArticle (`/rdbpublished/{id}`)
```json
{
  "@context": "https://schema.org",
  "@type": "ScholarlyArticle",
  "headline": "{{ $pub->pub_name }}",
  "name": "{{ $pub->pub_name }}",
  "abstract": "{{ $cleanAbstract }}",
  "author": [
    {
      "@type": "Person",
      "name": "..."
    }
  ],
  "datePublished": "2026-05-15",
  "isPartOf": {
    "@type": "Periodical",
    "name": "{{ $pub->pub_name_journal }}"
  },
  "image": "{{ $pub->cover_url }}",
  "publisher": {
    "@type": "Organization",
    "name": "มหาวิทยาลัยราชภัฏเพชรบูรณ์",
    "url": "{{ url('/') }}"
  }
}
```

### ResearchProject (`/rdbproject/{id}`)
```json
{
  "@context": "https://schema.org",
  "@type": "ResearchProject",
  "name": "{{ $project->pro_name_th }}",
  "alternateName": "{{ $project->pro_name_en }}",
  "description": "{{ $cleanAbstract }}",
  "funder": {
    "@type": "Organization",
    "name": "{{ $project->fund->fund_name ?? 'มหาวิทยาลัยราชภัฏเพชรบูรณ์' }}"
  },
  "principalInvestigator": {
    "@type": "Person",
    "name": "{{ $project->leader->researcher_name ?? '' }}"
  },
  "image": "{{ $project->cover_url }}"
}
```

### Person (`/rdbresearcher/{id}`)
```json
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "{{ $researcher->full_name }}",
  "jobTitle": "{{ $researcher->academic_position }}",
  "worksFor": {
    "@type": "EducationalOrganization",
    "name": "มหาวิทยาลัยราชภัฏเพชรบูรณ์"
  },
  "sameAs": [
    "https://orcid.org/{{ $researcher->orcid_id }}",
    "https://www.scopus.com/authid/detail.uri?authorId={{ $researcher->scopus_id }}"
  ]
}
```

---

## 3. Open Graph & Social Cards

Always utilize the high-resolution `.webp` full-page cover rasterized by `PdfCoverService`:
```blade
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ Str::limit($abstract, 160) }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $coverUrl }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ Str::limit($abstract, 160) }}">
<meta name="twitter:image" content="{{ $coverUrl }}">
```

---

## 4. XML Sitemaps Architecture

Use a dynamic XML sitemap index at `/sitemap.xml` that splits by category:
- `/sitemap-projects.xml`: Projects where `data_show = 1`
- `/sitemap-publications.xml`: Published articles
- `/sitemap-researchers.xml`: Public researcher profiles
- `/sitemap-utilizations.xml`: Research utilization records

All sitemaps must send `Content-Type: application/xml; charset=UTF-8` and use proper `<lastmod>` timestamps.

---

## 5. Robots.txt & Canonical Tag Standards

- Place `<link rel="canonical" href="{{ url()->current() }}">` on all public show pages.
- Ensure `robots.txt` permits crawling of all `/rdbproject/`, `/rdbpublished/`, `/rdbresearcher/`, and points directly to `Sitemap: {{ url('/sitemap.xml') }}`.
- For PDF downloads served through routes, ensure `X-Robots-Tag: all` is set in HTTP response headers so search engines can index full PDF documents.
