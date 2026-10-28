@extends('layouts.app')

@section('content')
@include('layouts.partials.scholar-meta', ['type' => 'published', 'item' => $item])
<div class="container-fluid py-4">

    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.rdbpublished.index') }}" class="text-decoration-none">ผลงานตีพิมพ์</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียดผลงาน</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>รายละเอียดผลงานตีพิมพ์
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column) -->
        <div class="col-lg-8">
            <!-- Title & Metadata Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header text-white p-3 p-md-4" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-file-earmark-text me-1"></i> ชื่อบทความ / ผลงานตีพิมพ์</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">{{ strip_tags((string) $item->pub_name) }}</h4>
                </div>
                <div class="card-body">
                    <!-- Badges Row -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($item->year)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                <i class="bi bi-calendar3 me-1"></i> ปี {{ $item->year->year_name }}
                            </span>
                        @endif
                        @if($item->pubtype)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                <i class="bi bi-bookmark me-1"></i> {{ $item->pubtype->pubtype_group }}
                            </span>
                            @if($item->pubtype->pubtype_subgroup)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1">
                                    {{ $item->pubtype->pubtype_subgroup }}
                                </span>
                            @endif
                        @endif
                        @if($item->branch)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1">
                                <i class="bi bi-diagram-3 me-1"></i> {{ $item->branch->branch_name }}
                            </span>
                        @endif
                    </div>

                    <!-- Additional Details Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @if($item->pub_date)
                                <tr>
                                    <th class="text-body-secondary fw-semibold" style="width: 25%;">วันที่เผยแพร่:</th>
                                    <td class="text-body">{{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}</td>
                                </tr>
                                @endif
                                @if($item->department)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">สังกัดหน่วยงาน:</th>
                                    <td class="text-body">{{ $item->department->department_nameTH }}</td>
                                </tr>
                                @endif
                                @if($item->pub_score)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">คะแนน/ค่าน้ำหนัก:</th>
                                    <td class="text-body">{{ number_format($item->pub_score, 2) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Journal / Publication Source -->
            @if($item->pub_name_journal)
            <div class="card shadow-sm border mb-4 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="me-3 fs-3 text-primary">
                            <i class="bi bi-journal-richtext"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-body mb-1">แหล่งตีพิมพ์ / วารสาร (Journal / Conference Source)</h6>
                            <p class="text-body-secondary mb-0 text-break">{{ strip_tags((string) $item->pub_name_journal) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Abstract Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-text-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">บทคัดย่อ (Abstract)</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @php
                        $abstracts = \App\Helpers\AbstractHelper::split($item->pub_abstract);
                    @endphp

                    @if(count($abstracts) >= 2 && !empty(trim(strip_tags($abstracts[0]))) && !empty(trim(strip_tags($abstracts[1]))))
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 mb-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        <i class="bi bi-card-text me-1"></i> บทคัดย่อ (ภาษาไทย)
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.7rem;">TH</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'pub-abstract-th')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกบทคัดย่อ
                                </button>
                            </div>
                            <div id="pub-abstract-th" class="abstract-content abstract-indent">
                                {!! strip_tags((string) $abstracts[0], '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') !!}
                            </div>
                        </div>
                        
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-info rounded-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        <i class="bi bi-translate me-1"></i> Abstract (English)
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.7rem;">EN</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'pub-abstract-en')">
                                    <i class="bi bi-clipboard me-1"></i> Copy Abstract
                                </button>
                            </div>
                            <div id="pub-abstract-en" class="abstract-content abstract-indent">
                                {!! strip_tags((string) $abstracts[1], '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') !!}
                            </div>
                        </div>
                    @else
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-file-text me-1"></i> บทคัดย่อ
                                </span>
                                @if(!empty(trim(strip_tags($item->pub_abstract ?? ''))))
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'pub-abstract-single')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกบทคัดย่อ
                                </button>
                                @endif
                            </div>
                            <div id="pub-abstract-single" class="abstract-content abstract-indent">
                                {!! !empty(trim(strip_tags($item->pub_abstract ?? ''))) ? strip_tags((string) $item->pub_abstract, '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') : '<span class="text-body-secondary fst-italic">ไม่มีข้อมูลบทคัดย่อ</span>' !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Sidebar / Right Column -->
        <div class="col-lg-4">
            {{-- 1. Related Project Card (If exists, shown at the top) --}}
            @if($item->project)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-folder-fill text-success me-2 fs-5"></i>
                    <h6 class="mb-0 fw-semibold text-success">โครงการวิจัยที่เกี่ยวข้อง</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold mb-2" style="font-size: 0.95rem; line-height: 1.5;">
                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-primary text-break">
                            {{ strip_tags((string) $item->project->pro_nameTH) }}
                        </a>
                    </h6>

                    @if($item->project->type || $item->project->typeSub)
                    <div class="text-body-secondary small mb-1">
                        {{ $item->project->type->pt_name ?? '' }}
                        @if($item->project->type && $item->project->typeSub) • @endif
                        {{ $item->project->typeSub->pts_name ?? '' }}
                    </div>
                    @endif

                    @if($item->project->department)
                    <div class="text-body-secondary small mb-2">
                        <i class="bi bi-building me-1"></i>{{ $item->project->department->department_nameTH }}
                    </div>
                    @endif

                    @php
                        $allWorks = $item->project->rdbProjectWorks ? $item->project->rdbProjectWorks->filter(fn($w) => !empty($w->researcher)) : collect();
                        $headWork = $allWorks->firstWhere('position_id', 2) 
                                 ?? $allWorks->firstWhere('position_id', 1) 
                                 ?? $allWorks->first();
                        $headResearcher = $headWork?->researcher;
                        $remainingWorks = $headResearcher ? $allWorks->reject(fn($w) => $w->researcher_id === $headResearcher->researcher_id)->unique('researcher_id') : collect();
                        $remainingCount = $remainingWorks->count();
                        $remainingNames = $remainingWorks->map(function($work) {
                            $prefix = $work->researcher->prefix->prefix_nameTH ?? '';
                            return e($prefix . $work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname);
                        })->filter()->implode('<br>• ');
                    @endphp
                    @if($headResearcher)
                    <hr class="my-2 border-secondary-subtle">
                    <div class="small fw-semibold text-body mb-1" style="font-size: 0.78rem;">หัวหน้าโครงการ:</div>
                    <div class="d-flex align-items-center flex-wrap">
                        <i class="bi bi-person-circle text-primary me-2 fs-6"></i>
                        <a href="{{ route('frontend.rdbresearcher.show', $headResearcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-primary fw-medium small">
                            {{ $headResearcher->prefix->prefix_nameTH ?? '' }}{{ $headResearcher->researcher_fname }} {{ $headResearcher->researcher_lname }}
                        </a>
                        @if($remainingCount > 0)
                            <span class="text-body-secondary small ms-1" 
                                  data-bs-toggle="tooltip" 
                                  data-bs-placement="top" 
                                  data-bs-html="true"
                                  title="<strong>นักวิจัยร่วม:</strong><br>• {!! strip_tags($remainingNames, '<br>') !!}"
                                  style="cursor: pointer;">(+{{ $remainingCount }})</span>
                        @endif
                    </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูรายละเอียดโครงการ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. Authors Card --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-people-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ผู้เขียนบทความ (Authors)</h6>
                    @if($item->authors && $item->authors->count() > 0)
                        <span class="badge bg-primary-subtle text-primary ms-auto">{{ $item->authors->count() }}</span>
                    @elseif($item->researcher)
                        <span class="badge bg-primary-subtle text-primary ms-auto">1</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($item->authors && $item->authors->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($item->authors as $author)
                                <div class="list-group-item bg-transparent py-2.5 px-3">
                                    <div class="d-flex align-items-start">
                                        <div class="me-2 text-primary fs-5 mt-0.5">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex flex-wrap align-items-center gap-1.5 mb-0.5">
                                                <a href="{{ route('frontend.rdbresearcher.show', $author->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold text-body hover-primary">
                                                    {{ $author->prefix->prefix_nameTH ?? '' }}{{ $author->researcher_fname }} {{ $author->researcher_lname }}
                                                </a>
                                                @if(isset($author->authorType))
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem; font-weight: 500; padding: 0.16rem 0.45rem;">
                                                        {{ $author->authorType->pubta_nameTH }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($author->department)
                                                <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                    <i class="bi bi-building me-1"></i>{{ $author->department->department_nameTH }}
                                                </small>
                                            @elseif($author->researcher_note)
                                                <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                    {{ $author->researcher_note }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($item->researcher)
                        <div class="list-group list-group-flush">
                            <div class="list-group-item bg-transparent py-2.5 px-3">
                                <div class="d-flex align-items-start">
                                    <div class="me-2 text-primary fs-5 mt-0.5">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-1.5 mb-0.5">
                                            <a href="{{ route('frontend.rdbresearcher.show', $item->researcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold text-body hover-primary">
                                                {{ $item->researcher->prefix->prefix_nameTH ?? '' }}{{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                            </a>
                                        </div>
                                        @if($item->department)
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                <i class="bi bi-building me-1"></i>{{ $item->department->department_nameTH }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 text-body-secondary text-center">ไม่มีข้อมูลผู้เขียน</div>
                    @endif
                </div>
            </div>

            {{-- 2. Files Card (High-Impact CTA Card) --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ไฟล์เอกสาร (Files)</h6>
                </div>
                <div class="card-body p-3">
                    @php
                        $filePath = $item->getPublishedFilePath();
                    @endphp
                    @if($filePath)
                        <a href="{{ route('frontend.rdbpublished.view_file', $item->id) }}" 
                           target="_blank" 
                           class="file-cta-card card border border-success-subtle text-decoration-none shadow-sm"
                           style="background: rgba(16, 185, 129, 0.04);">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2.5">
                                    <div class="d-flex align-items-center min-w-0 me-2">
                                        <div class="file-icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h6 class="fw-bold text-success mb-0 text-truncate" style="font-size: 0.98rem;">บทความฉบับเต็ม</h6>
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">Full Paper / Publication (PDF)</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" title="ดาวน์โหลด: {{ number_format($item->pub_download ?? 0) }} ครั้ง" style="font-size: 0.76rem;">
                                            <i class="bi bi-download me-1"></i>{{ number_format($item->pub_download ?? 0) }}
                                        </span>
                                        @if(!empty($item->pub_numfile))
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-1.5 py-0.5" style="font-size: 0.7rem; font-variant-numeric: tabular-nums;">
                                                <i class="bi bi-file-earmark-text me-1"></i>{{ $item->pub_numfile }} หน้า
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="btn btn-sm btn-success w-100 rounded-2 fw-medium d-flex align-items-center justify-content-center gap-1.5 py-1.5 text-white shadow-sm">
                                    <i class="bi bi-cloud-arrow-down-fill"></i> ดาวน์โหลดบทความฉบับเต็ม
                                </div>
                            </div>
                        </a>
                    @else
                        <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-0 text-body-secondary small d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark text-muted fs-5 me-2"></i>
                            <span>ไม่มีไฟล์เอกสารแนบ</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Citations Card (Academic Standards: APA 7, IEEE, MLA 9) -->
            @php
                $pubAuthorsList = [];
                if ($item->authors && $item->authors->count() > 0) {
                    foreach ($item->authors as $a) {
                        $name = trim($a->researcher_fname . ' ' . $a->researcher_lname);
                        if (!empty($name)) {
                            $pubAuthorsList[] = $name;
                        }
                    }
                } elseif ($item->researcher) {
                    $pubAuthorsList[] = trim($item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname);
                }
                if (empty($pubAuthorsList)) {
                    $pubAuthorsList[] = 'มหาวิทยาลัยราชภัฏเพชรบูรณ์';
                }

                $pubYearBE = $item->year->year_name ?? (date('Y') + 543);
                $pubCleanTitle = trim(preg_replace('/\s+/', ' ', strip_tags($item->pub_name)));
                $journalName = $item->pub_name_journal ? trim(strip_tags($item->pub_name_journal)) : 'มหาวิทยาลัยราชภัฏเพชรบูรณ์';

                // APA 7: Author. (Year). Article title. Journal Name.
                $countPubAuthors = count($pubAuthorsList);
                if ($countPubAuthors === 1) {
                    $apaPubAuthors = $pubAuthorsList[0];
                } elseif ($countPubAuthors === 2) {
                    $apaPubAuthors = $pubAuthorsList[0] . ' และ ' . $pubAuthorsList[1];
                } else {
                    $apaPubAuthors = implode(', ', array_slice($pubAuthorsList, 0, -1)) . ', และ ' . end($pubAuthorsList);
                }
                $pubCitationApa = $apaPubAuthors . '. (' . $pubYearBE . '). ' . $pubCleanTitle . '. <i>' . $journalName . '</i>.';

                // IEEE: [1] Author, "Article title," Journal Name, Year.
                if ($countPubAuthors === 1) {
                    $ieeePubAuthors = $pubAuthorsList[0];
                } elseif ($countPubAuthors === 2) {
                    $ieeePubAuthors = $pubAuthorsList[0] . ' และ ' . $pubAuthorsList[1];
                } else {
                    $ieeePubAuthors = $pubAuthorsList[0] . ' และคณะ';
                }
                $pubCitationIeee = '[1] ' . $ieeePubAuthors . ', "' . $pubCleanTitle . '," <i>' . $journalName . '</i>, ' . $pubYearBE . '.';

                // MLA 9: Author. "Article Title." Journal Name, Year.
                if ($countPubAuthors === 1) {
                    $mlaPubAuthors = $pubAuthorsList[0];
                } elseif ($countPubAuthors === 2) {
                    $mlaPubAuthors = $pubAuthorsList[0] . ', และ ' . $pubAuthorsList[1];
                } else {
                    $mlaPubAuthors = $pubAuthorsList[0] . ', และคณะ';
                }
                $pubCitationMla = $mlaPubAuthors . '. "' . $pubCleanTitle . '." <i>' . $journalName . '</i>, ' . $pubYearBE . '.';
            @endphp
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-quote text-primary me-2 fs-5"></i>
                        <h6 class="mb-0 fw-semibold text-body">การอ้างอิง (Citations)</h6>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">3 รูปแบบ</span>
                </div>
                <div class="card-body p-3">
                    <!-- Segmented Small Tabs -->
                    <ul class="nav nav-pills nav-fill gap-1 p-1 bg-body-tertiary rounded-2 mb-2.5 border" id="pubCitationTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-2 rounded-2 fw-semibold" id="tab-pub-apa7" data-bs-toggle="pill" data-bs-target="#pane-pub-apa7" type="button" role="tab" aria-controls="pane-pub-apa7" aria-selected="true" style="font-size: 0.78rem;">
                                APA 7
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-2 rounded-2 fw-semibold" id="tab-pub-ieee" data-bs-toggle="pill" data-bs-target="#pane-pub-ieee" type="button" role="tab" aria-controls="pane-pub-ieee" aria-selected="false" style="font-size: 0.78rem;">
                                IEEE
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-2 rounded-2 fw-semibold" id="tab-pub-mla9" data-bs-toggle="pill" data-bs-target="#pane-pub-mla9" type="button" role="tab" aria-controls="pane-pub-mla9" aria-selected="false" style="font-size: 0.78rem;">
                                MLA 9
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Panes -->
                    <div class="tab-content" id="pubCitationTabContent">
                        <!-- APA 7 Pane -->
                        <div class="tab-pane fade show active" id="pane-pub-apa7" role="tabpanel" aria-labelledby="tab-pub-apa7" tabindex="0">
                            <div class="p-2.5 rounded bg-body-tertiary border text-body text-break mb-2" id="text-pub-apa7" style="font-size: 0.82rem; line-height: 1.65; user-select: all;">
                                {!! strip_tags((string) $pubCitationApa, '<i><em><b><strong>') !!}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;" onclick="copyCitation('text-pub-apa7', this)">
                                <i class="bi bi-clipboard"></i> คัดลอก APA 7
                            </button>
                        </div>

                        <!-- IEEE Pane -->
                        <div class="tab-pane fade" id="pane-pub-ieee" role="tabpanel" aria-labelledby="tab-pub-ieee" tabindex="0">
                            <div class="p-2.5 rounded bg-body-tertiary border text-body text-break mb-2" id="text-pub-ieee" style="font-size: 0.82rem; line-height: 1.65; user-select: all;">
                                {!! strip_tags((string) $pubCitationIeee, '<i><em><b><strong>') !!}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;" onclick="copyCitation('text-pub-ieee', this)">
                                <i class="bi bi-clipboard"></i> คัดลอก IEEE
                            </button>
                        </div>

                        <!-- MLA 9 Pane -->
                        <div class="tab-pane fade" id="pane-pub-mla9" role="tabpanel" aria-labelledby="tab-pub-mla9" tabindex="0">
                            <div class="p-2.5 rounded bg-body-tertiary border text-body text-break mb-2" id="text-pub-mla9" style="font-size: 0.82rem; line-height: 1.65; user-select: all;">
                                {!! strip_tags((string) $pubCitationMla, '<i><em><b><strong>') !!}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;" onclick="copyCitation('text-pub-mla9', this)">
                                <i class="bi bi-clipboard"></i> คัดลอก MLA 9
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Keywords Card -->
            @if($item->pub_keyword)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-tags-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">คำสำคัญ (Keywords)</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(explode(',', $item->pub_keyword) as $keyword)
                            @if(trim($keyword))
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-1 text-wrap text-break">
                                    {{ trim($keyword) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Note / หมายเหตุ --}}
            @if($item->pub_note)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-sticky text-warning me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">หมายเหตุ</h5>
                </div>
                <div class="card-body">
                    <div class="text-body text-break lh-lg">
                        {!! nl2br(e($item->pub_note)) !!}
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
    .hover-underline:hover {
        text-decoration: underline !important;
    }

    /* High-Impact File CTA Card Styling */
    .file-cta-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        border-radius: 10px;
    }
    .file-cta-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.12) !important;
    }
    .file-cta-card:active {
        transform: translateY(0);
    }

    /* Abstract Typography & Box Styling */
    .abstract-box {
        border-radius: 12px;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .abstract-box:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    .abstract-content {
        font-size: 0.96rem;
        line-height: 1.88;
        letter-spacing: 0.012em;
        text-align: justify;
        text-justify: inter-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .abstract-indent {
        text-indent: 2.25rem;
    }
    .abstract-content p {
        margin-bottom: 0.85rem;
        text-indent: 2.25rem;
    }
    [data-bs-theme="dark"] .abstract-content {
        color: #d1d5db; /* Softer, comfortable reading tone in dark mode */
    }
    [data-bs-theme="light"] .abstract-content {
        color: #334155; /* Slate-700 for optimal contrast without eye strain */
    }
    .copy-abstract-btn {
        transition: all 0.15s ease;
    }
    .copy-abstract-btn:hover {
        background-color: var(--bs-secondary-bg);
        color: var(--bs-body-color);
    }
</style>

<script>
    function copyTextToClipboard(text, onSuccess, onError) {
        if (!text) return;
        text = text.trim();

        // 1. Modern Clipboard API (available in HTTPS or localhost)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                if (typeof onSuccess === 'function') onSuccess();
            }).catch(() => {
                execFallback(text, onSuccess, onError);
            });
        } else {
            // 2. Fallback for non-secure contexts (e.g. http:// domain in Laragon / local dev)
            execFallback(text, onSuccess, onError);
        }

        function execFallback(str, successCb, errorCb) {
            let textarea = document.createElement('textarea');
            textarea.value = str;
            textarea.style.position = 'fixed';
            textarea.style.top = '0';
            textarea.style.left = '0';
            textarea.style.width = '2em';
            textarea.style.height = '2em';
            textarea.style.padding = '0';
            textarea.style.border = 'none';
            textarea.style.outline = 'none';
            textarea.style.boxShadow = 'none';
            textarea.style.background = 'transparent';
            textarea.style.opacity = '0';
            textarea.setAttribute('readonly', '');
            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            let successful = false;
            try {
                successful = document.execCommand('copy');
            } catch (err) {
                console.error('execCommand copy failed:', err);
            }
            document.body.removeChild(textarea);

            if (successful) {
                if (typeof successCb === 'function') successCb();
            } else {
                if (typeof errorCb === 'function') errorCb();
            }
        }
    }

    function copyAbstractText(target, triggerBtn) {
        let btn = (triggerBtn && triggerBtn.nodeType) ? triggerBtn : (target && target.nodeType ? target : null);
        let elId = (typeof target === 'string') ? target : (typeof triggerBtn === 'string' ? triggerBtn : null);
        if (!elId) return;
        const el = document.getElementById(elId);
        if (!el) return;
        const text = el.innerText || el.textContent || '';

        copyTextToClipboard(text, () => {
            if (!btn) return;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> คัดลอกแล้ว';
            btn.classList.add('border-success');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('border-success');
            }, 2000);
        }, () => {
            window.prompt("กด Ctrl+C เพื่อคัดลอกข้อความ:", text);
        });
    }

    function copyCitation(target, triggerBtn) {
        let btn = (triggerBtn && triggerBtn.nodeType) ? triggerBtn : (target && target.nodeType ? target : null);
        let elId = (typeof target === 'string') ? target : (typeof triggerBtn === 'string' ? triggerBtn : null);
        if (!elId) return;
        const el = document.getElementById(elId);
        if (!el) return;
        const text = el.innerText || el.textContent || '';

        copyTextToClipboard(text, () => {
            if (!btn) return;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> คัดลอกการอ้างอิงแล้ว';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }, () => {
            window.prompt("กด Ctrl+C เพื่อคัดลอกข้อความการอ้างอิง:", text);
        });
    }
</script>
@endsection