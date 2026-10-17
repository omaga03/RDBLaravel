@extends('layouts.app')

@section('content')
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
            <h3 class="mb-0 fw-bold text-body">
                <i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>รายละเอียดผลงานตีพิมพ์
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column) -->
        <div class="col-lg-8">
            <!-- Title & Metadata Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);">
                    <span class="small text-white-50"><i class="bi bi-file-earmark-text me-1"></i> ชื่อบทความ / ผลงานตีพิมพ์</span>
                    <h4 class="card-title mb-0 mt-1 fw-bold text-white text-break">{!! $item->pub_name !!}</h4>
                </div>
                <div class="card-body">
                    <!-- Badges Row -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($item->year)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                <i class="bi bi-calendar3 me-1"></i> ปี {{ $item->year->year_name }}
                            </span>
                        @endif
                        @if($item->pubtype)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                <i class="bi bi-bookmark me-1"></i> {{ $item->pubtype->pubtype_group }}
                            </span>
                            @if($item->pubtype->pubtype_subgroup)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-3 py-2">
                                    {{ $item->pubtype->pubtype_subgroup }}
                                </span>
                            @endif
                        @endif
                        @if($item->branch)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2">
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
                            <p class="text-body-secondary mb-0 text-break">{!! $item->pub_name_journal !!}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Abstract Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-file-text-fill text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">บทคัดย่อ (Abstract)</h5>
                </div>
                <div class="card-body">
                    @php
                        // Split abstract by 3 or more consecutive <br> tags if bilingual
                        $abstracts = preg_split('/(<br\s*\/?>\\s*){3,}/i', $item->pub_abstract ?? '');
                    @endphp

                    @if(count($abstracts) >= 2)
                        <div class="p-3 bg-body-tertiary border rounded mb-3">
                            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-card-text me-1"></i> บทคัดย่อ (ภาษาไทย)</h6>
                            <div class="text-body text-break lh-base">{!! $abstracts[0] !!}</div>
                        </div>
                        
                        <div class="p-3 bg-body-secondary border rounded">
                            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-translate me-1"></i> Abstract (English)</h6>
                            <div class="text-body text-break lh-base">{!! $abstracts[1] !!}</div>
                        </div>
                    @else
                        <div class="p-3 bg-body-tertiary border rounded">
                            <div class="text-body text-break lh-base">
                                {!! !empty(trim(strip_tags($item->pub_abstract ?? ''))) ? $item->pub_abstract : '<span class="text-body-secondary">ไม่มีข้อมูลบทคัดย่อ</span>' !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Full Paper / Downloads -->
            @php
                $filePath = $item->getPublishedFilePath();
            @endphp
            @if($filePath)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-download text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">เอกสารแนบ / ไฟล์บทความฉบับเต็ม</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-body-tertiary flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-2 me-3"></i>
                            <div>
                                <a href="{{ asset('storage/' . $filePath) }}" class="text-decoration-none fw-bold text-primary fs-6" target="_blank">
                                    <i class="bi bi-box-arrow-up-right me-1 small"></i> Full Paper / บทความฉบับเต็ม
                                </a>
                                <small class="text-body-secondary d-block">คลิกเพื่อเปิดอ่านหรือดาวน์โหลดเอกสาร</small>
                            </div>
                        </div>
                        <div class="text-body-secondary small">
                            <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1">
                                <i class="bi bi-eye me-1"></i> ดาวน์โหลด: {{ number_format($item->pub_download ?? 0) }} ครั้ง
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar / Right Column -->
        <div class="col-lg-4">
            <!-- Authors Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-people-fill text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">ผู้เขียนบทความ (Authors)</h5>
                </div>
                <div class="card-body p-0">
                    @if($item->authors && $item->authors->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($item->authors as $author)
                                <div class="list-group-item bg-transparent py-3 px-3">
                                    <div class="d-flex align-items-start">
                                        <div class="me-2 text-primary fs-5">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <a href="{{ route('frontend.rdbresearcher.show', $author->researcher_id) }}" class="text-decoration-none fw-bold text-body hover-primary d-block">
                                                {{ $author->prefix->prefix_nameTH ?? '' }}{{ $author->researcher_fname }} {{ $author->researcher_lname }}
                                            </a>
                                            <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                                @if(isset($author->authorType))
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.75rem;">
                                                        {{ $author->authorType->pubta_nameTH }}
                                                    </span>
                                                @endif
                                                @if($author->department)
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.75rem;">
                                                        {{ $author->department->department_nameTH }}
                                                    </span>
                                                @elseif($author->researcher_note)
                                                    <span class="text-body-secondary small">({{ $author->researcher_note }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($item->researcher)
                        <div class="list-group list-group-flush">
                            <div class="list-group-item bg-transparent py-3 px-3">
                                <div class="d-flex align-items-start">
                                    <div class="me-2 text-primary fs-5">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('frontend.rdbresearcher.show', $item->researcher->researcher_id) }}" class="text-decoration-none fw-bold text-body hover-primary">
                                            {{ $item->researcher->prefix->prefix_nameTH ?? '' }}{{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                        </a>
                                        @if($item->department)
                                            <span class="d-block text-body-secondary small">{{ $item->department->department_nameTH }}</span>
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

            <!-- Related Project Card -->
            @if($item->project)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-folder2-open text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">โครงการวิจัยที่เกี่ยวข้อง</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">
                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" class="text-decoration-none text-body hover-primary text-break">
                            {!! $item->project->pro_nameTH !!}
                        </a>
                    </h6>
                    @if($item->project->pro_nameEN)
                        <p class="text-body-secondary small mb-2 text-break">{!! $item->project->pro_nameEN !!}</p>
                    @endif
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @if($item->project->pro_code)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">รหัส: {{ $item->project->pro_code }}</span>
                        @endif
                        @if($item->project->year)
                            <span class="badge bg-info-subtle text-info-emphasis">ปี {{ $item->project->year->year_name }}</span>
                        @endif
                        @if($item->project->pro_budget)
                            <span class="badge bg-success-subtle text-success">฿{{ number_format($item->project->pro_budget, 2) }}</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูรายละเอียดโครงการ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Keywords Card -->
            @if($item->pub_keyword)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-tags-fill text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">คำสำคัญ (Keywords)</h5>
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

            <!-- Navigation Back Button -->
            <div class="d-grid gap-2">
                <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการผลงานตีพิมพ์
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>
@endsection