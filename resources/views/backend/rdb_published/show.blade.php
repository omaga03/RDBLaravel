@extends('layouts.app')

@section('content')
<!-- TomSelect CDN for searchable dropdown -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    /* Print Styles */
    @media print {
        .d-print-none { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger, .bg-secondary, .bg-dark {
            background-color: transparent !important;
            color: #000 !important;
            border: 1px solid #dee2e6;
        }
        .badge { border: 1px solid #000; color: #000 !important; }
    }
    
    /* Dark Mode Text Support */
    
    /* Match TomSelect and form-select heights to 40px */
    .ts-wrapper .ts-control {
        min-height: 38px !important;
        max-height: 38px !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem;
        line-height: 1.5;
        box-sizing: border-box;
    }
    .ts-wrapper .ts-control > input {
        margin: 0 !important;
        padding: 0 !important;
    }
    #authorTypeSelect,
    #authorNameDisplay {
        height: 40px !important;
    }
</style>

<div class="py-4">
    <div class="row">
        <!-- Header & Actions -->
        <x-page-header 
            title="รายละเอียดการตีพิมพ์ (Publication Details)"
            icon="bi-journal-check"
            :backRoute="route('backend.rdb_published.index')"
            :editRoute="route('backend.rdb_published.edit', $item->id)"
            :deleteRoute="route('backend.rdb_published.destroy', $item->id)"
            :showPrint="true"
        />

        <div class="col-lg-8 order-1 order-lg-1">
            <!-- General Information -->
            <div class="card shadow-sm mb-4 border">
                <div class="card-header text-white p-3 p-md-4 d-print-none" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-file-earmark-text me-1"></i> ชื่อบทความ / ผลงานตีพิมพ์</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">{{ $item->pub_name }}</h4>
                    @if($item->pub_name_journal)
                        <p class="text-white-50 small mb-0 mt-1 text-break"><i class="bi bi-journal-bookmark me-1"></i> {{ $item->pub_name_journal }}</p>
                    @endif
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
                        @if($item->pub_score && floatval($item->pub_score) > 0)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1">
                                <i class="bi bi-star me-1"></i> คะแนน: {{ $item->pub_score }}
                            </span>
                        @endif
                    </div>

                        <div class="col-md-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2">รายละเอียดเพิ่มเติม</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th style="width: 30%;">ประเภทผลงาน:</th>
                                    <td>
                                        @if($item->pubtype)
                                            {{ $item->pubtype->pubtype_group ?? '-' }}
                                            <i class="bi bi-chevron-right text-muted small"></i>
                                            {{ $item->pubtype->pubtype_grouptype ?? '-' }}
                                            @if($item->pubtype->pubtype_subgroup)
                                                <i class="bi bi-chevron-right text-muted small"></i>
                                                {{ $item->pubtype->pubtype_subgroup }}
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>งบประมาณ:</th>
                                    <td class="text-success fw-bold">{{ number_format($item->pub_budget, 2) }} บาท</td>
                                </tr>
                                <tr>
                                    <th>วันที่ตีพิมพ์:</th>
                                    <td>
                                        {{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}
                                        @if($item->pub_date_end)
                                            - {{ \App\Helpers\ThaiDateHelper::format($item->pub_date_end, false, true) }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>หน่วยงาน:</th>
                                    <td>{{ $item->department->department_nameTH ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>สาขาวิชาการ:</th>
                                    <td>{{ $item->branch->branch_name ?? '-' }}</td>
                                </tr>
                                @if($item->pub_keyword)
                                <tr>
                                    <th>คำสำคัญ (Keywords):</th>
                                    <td>
                                        @foreach(explode(',', $item->pub_keyword) as $keyword)
                                            <span class="badge bg-secondary me-1 mb-1">{{ trim($keyword) }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                                @if($item->project)
                                <tr>
                                    <th>โครงการที่เกี่ยวข้อง:</th>
                                    <td>
                                        <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-folder-symlink"></i> {{ $item->project->pro_nameTH }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>จำนวนการเข้าดู:</th>
                                    <td>{{ number_format($item->pub_download ?? 0) }} ครั้ง</td>
                                </tr>
                            </table>
                        </div>

                        @if($item->pub_abstract)
                        <div class="col-md-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2">บทคัดย่อ (Abstract)</h6>
                            @php
                                $separator = '<br><br><br><br>';
                                if (!str_contains($item->pub_abstract, $separator) && str_contains($item->pub_abstract, '<br><br><br>')) {
                                    $separator = '<br><br><br>';
                                }
                                $abstractParts = explode($separator, $item->pub_abstract);
                            @endphp

                            <style>
                                [data-bs-theme="dark"] .bg-abstract {
                                    background-color: #2b3035 !important;
                                    color: #dee2e6;
                                }
                                [data-bs-theme="light"] .bg-abstract {
                                    background-color: #f8f9fa !important;
                                    color: #212529;
                                }
                                @media print {
                                    .bg-abstract {
                                        border: 1px solid #dee2e6 !important;
                                        background-color: #fff !important;
                                        color: #000 !important;
                                    }
                                }
                            </style>

                            @if(!empty($abstractParts[0]))
                            <div class="mb-3">
                                <span class="badge bg-primary mb-2 d-print-none">บทคัดย่อภาษาไทย</span>
                                <strong class="d-none d-print-block">บทคัดย่อภาษาไทย</strong>
                                <div class="bg-abstract p-3 rounded border" style="min-height: 100px; height: auto; overflow: visible; overflow-wrap: break-word; word-wrap: break-word;">
                                    {!! strip_tags($abstractParts[0], '<p><br><b><strong><i><em><u>') !!}
                                </div>
                            </div>
                            @endif

                            @if(!empty($abstractParts[1]))
                            <div>
                                <span class="badge bg-info text-dark mb-2 d-print-none">บทคัดย่อภาษาอังกฤษ</span>
                                <strong class="d-none d-print-block">บทคัดย่อภาษาอังกฤษ</strong>
                                <div class="bg-abstract p-3 rounded border" style="min-height: 100px; height: auto; overflow: visible; overflow-wrap: break-word; word-wrap: break-word;">
                                    {!! strip_tags($abstractParts[1], '<p><br><b><strong><i><em><u>') !!}
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        @if($item->pub_note)
                         <div class="col-md-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2">หมายเหตุ (Note)</h6>
                            <div class="text-muted">
                                {{ $item->pub_note }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Authors / Researchers -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white d-print-none d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> ผู้แต่ง/นักวิจัย (Authors)</h5>
                    <button class="btn btn-light btn-sm text-success fw-bold" data-bs-toggle="modal" data-bs-target="#authorModal" data-mode="add">
                        <i class="bi bi-plus-circle"></i> เพิ่ม (Add)
                    </button>
                </div>
                 <div class="card-header bg-success text-white d-none d-print-block">
                     <h5 class="mb-0">ผู้แต่ง/นักวิจัย (Authors)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>บทบาท (Role)</th>
                                    <th class="d-print-none text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->authors as $index => $author)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('backend.rdb_researcher.show', $author->researcher_id) }}" class="fw-bold text-body hover-primary text-decoration-none">
                                             {{ $author->researcher_fname }} {{ $author->researcher_lname }}
                                        </a>
                                        <small class="text-body-secondary d-block">
                                            {{ $author->department->department_nameTH ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if(isset($authorTypes[$author->pivot->pubta_id]))
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $authorTypes[$author->pivot->pubta_id] }}</span>
                                        @else
                                            -
                                        @endif

                                        @if($author->pivot->pubw_main == 1)
                                            <span class="ms-1" title="หน่วยงานหลัก (Main Office)">🏠</span>
                                        @endif
                                        @if($author->pivot->pubw_bud == 1)
                                            <span class="ms-1" title="ผู้ได้รับการสนับสนุนงบประมาณ (Budget Support)">💰</span>
                                        @endif
                                    </td>
                                    <td class="text-end d-print-none" style="width: 100px;">
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#authorModal"
                                            data-mode="edit"
                                            data-researcher-id="{{ $author->researcher_id }}"
                                            data-researcher-name="{{ $author->researcher_fname }} {{ $author->researcher_lname }}"
                                            data-pubta-id="{{ $author->pivot->pubta_id }}"
                                            data-pubw-main="{{ $author->pivot->pubw_main }}"
                                            data-pubw-bud="{{ $author->pivot->pubw_bud }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('backend.rdb_published.destroy_author', ['id' => $item->id, 'researcher_id' => $author->researcher_id]) }}" 
                                              method="POST" class="d-inline" onsubmit="return confirm('ยืนยันเลิกอ้างอิงนักวิจัยท่านนี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                    <td colspan="4" class="text-center text-muted py-3">ไม่มีข้อมูลผู้แต่ง</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



        </div>

        <div class="col-lg-4 order-2 order-lg-2">
            <!-- File Attachment -->
             <div class="card shadow-sm mb-4 d-print-none">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-paperclip"></i> ไฟล์แนบ (File Attachment)</h5>
                </div>
                <div class="card-body text-center p-4">
                    @if($item->pub_file)
                        <div class="mb-3">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="text-break mb-2">{{ $item->pub_file }}</h6>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-eye"></i> เปิดดูไฟล์ {{ number_format($item->pub_view_file ?? 0) }} ครั้ง
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ asset('storage/uploads/rdb_published/' . $item->pub_file) }}" 
                               target="_blank" 
                               class="btn btn-primary"
                               onclick="fetch('{{ route('backend.rdb_published.view_file', $item->id) }}');">
                                <i class="bi bi-eye"></i> เปิดดูไฟล์
                            </a>
                            <form action="{{ route('backend.rdb_published.delete_file', $item->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบไฟล์?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> ลบไฟล์
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-muted py-3">
                            <i class="bi bi-file-earmark-x" style="font-size: 2rem;"></i><br>
                            ไม่มีไฟล์แนบ
                        </div>
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-cloud-upload"></i> อัปโหลดไฟล์ Full Paper
                        </button>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="mt-4">
                @php
                    $createdBy = $item->user_created ? \App\Models\User::find($item->user_created) : null;
                    $updatedBy = $item->user_updated ? \App\Models\User::find($item->user_updated) : null;
                    $createdByName = $createdBy?->researcher ? ($createdBy->researcher->researcher_fname . ' ' . $createdBy->researcher->researcher_lname) : ($createdBy?->username ?? '-');
                    $updatedByName = $updatedBy?->researcher ? ($updatedBy->researcher->researcher_fname . ' ' . $updatedBy->researcher->researcher_lname) : ($updatedBy?->username ?? '-');
                @endphp
                <x-system-info :created_at="$item->created_at" :created_by="$createdByName" :updated_at="$item->updated_at" :updated_by="$updatedByName" />
            </div>
        </div>
    </div>

    <!-- Bottom Buttons -->
    <x-action-buttons 
        :backRoute="route('backend.rdb_published.index')"
        :editRoute="route('backend.rdb_published.edit', $item->id)"
        :deleteRoute="route('backend.rdb_published.destroy', $item->id)"
        :showPrint="true"
    />
</div>

<!-- Modals -->

<!-- Unified Author Manager Modal -->
<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorModalLabel"><i class="bi bi-people-fill"></i> จัดการผู้แต่ง/นักวิจัย (Manage Authors)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add/Edit Form -->
                <div class="card mb-3 border">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" id="authorFormTitle"><i class="bi bi-person-plus"></i> เพิ่มผู้แต่งใหม่ (Add New)</h6>
                        <form id="authorForm" method="POST" action="{{ route('backend.rdb_published.store_author', $item->id) }}">
                            @csrf
                            <input type="hidden" name="_method" id="authorFormMethod" value="POST">
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small required">นักวิจัย (Researcher)</label>
                                    <!-- Select2 HTML kept same -->
                                    <select name="researcher_id" id="authorSelect" class="form-select select2-ajax" required style="width: 100%;">
                                    </select>
                                    <input type="text" id="authorNameDisplay" class="form-control d-none" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small required">ประเภทผู้แต่ง (Author Type)</label>
                                    <select name="pubta_id" id="authorTypeSelect" class="form-select" required>
                                        @foreach($authorTypes as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="pubw_main" value="1" id="checkMain">
                                            <label class="form-check-label small" for="checkMain">
                                                หน่วยงานหลัก (Main Office)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="pubw_bud" value="1" id="checkBud">
                                            <label class="form-check-label small" for="checkBud">
                                                ผู้ได้รับการสนับสนุนงบประมาณ (Budget Support)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-end mt-2">
                                    <button type="button" class="btn btn-secondary btn-sm" id="btnCancelEdit" style="display: none;" onclick="resetAuthorForm()">
                                        <i class="bi bi-x-circle"></i> ยกเลิก
                                    </button>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-save"></i> บันทึกข้อมูล
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Upload File Modal (Unchanged) -->
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--rdb-gradient-primary);">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>อัปโหลดไฟล์ Full Paper</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('backend.rdb_published.upload_file', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">เลือกไฟล์ (PDF เท่านั้น, สูงสุด 20MB)</label>
                        <input type="file" name="pub_file" class="form-control" accept=".pdf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">อัปโหลด</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Author Modal -->
<div class="modal fade" id="editAuthorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--rdb-gradient-primary);">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลผู้แต่ง/นักวิจัย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAuthorForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">นักวิจัย</label>
                        <input type="text" id="edit_researcher_name" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">ประเภทผู้แต่ง</label>
                        <select name="pubta_id" id="edit_pubta_id" class="form-select" required>
                            @foreach($authorTypes as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="pubw_main" value="1" id="edit_checkMain">
                            <label class="form-check-label" for="edit_checkMain">
                                หน่วยงานหลัก (Main Office)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="pubw_bud" value="1" id="edit_checkBud">
                            <label class="form-check-label" for="edit_checkBud">
                                ผู้ได้รับการสนับสนุนงบประมาณ (Budget Support)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    // Track existing assignments (global scope for access from resetAuthorForm)
    var hasMainOffice = {{ $item->authors->contains(fn($a) => $a->pivot->pubw_main == 1) ? 'true' : 'false' }};
    var hasBudgetSupport = {{ $item->authors->contains(fn($a) => $a->pivot->pubw_bud == 1) ? 'true' : 'false' }};
    var mainOfficeResearcherId = {{ $item->authors->firstWhere(fn($a) => $a->pivot->pubw_main == 1)?->researcher_id ?? 'null' }};
    var budgetSupportResearcherId = {{ $item->authors->firstWhere(fn($a) => $a->pivot->pubw_bud == 1)?->researcher_id ?? 'null' }};
    
    // Track taken author types and single-only types
    var takenAuthorTypes = {!! json_encode($item->authors->pluck('pivot.pubta_id')->unique()->values()) !!};
    var singleOnlyTypes = [1]; // ผู้เขียนหลัก (First Author) - can only have one
    
    // Track existing researcher IDs to prevent duplicates
    var existingResearcherIds = {!! json_encode($item->authors->pluck('researcher_id')->unique()->values()) !!};
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TomSelect for Researcher Search
        var authorSelectElement = document.getElementById('authorSelect');
        var tomSelectInstance = null;
        
        if (authorSelectElement) {
            tomSelectInstance = new TomSelect('#authorSelect', {
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                maxOptions: 10,
                placeholder: 'พิมพ์ชื่อนักวิจัยเพื่อค้นหา...',
                load: function(query, callback) {
                    if (!query.length || query.length < 2) return callback();
                    
                    fetch("{{ route('backend.rdb_published.search_researcher') }}?q=" + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(json => {
                            // Filter out researchers that are already authors
                            var filtered = json.results.filter(function(item) {
                                return existingResearcherIds.indexOf(parseInt(item.id)) === -1;
                            });
                            callback(filtered);
                        })
                        .catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return '<div>' + (item._highlight || escape(item.text)) + '</div>';
                    },
                    item: function(item, escape) {
                        return '<div>' + escape(item.text) + '</div>';
                    }
                }
            });
            
            // Store instance globally for later use
            window.authorTomSelect = tomSelectInstance;
        }
        
        // Handle Modal Show Event
        var authorModal = document.getElementById('authorModal');
        if (authorModal) {
            authorModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                if (!button) return;
                
                // If clicked on icon inside button, get the parent button
                if (button.tagName !== 'BUTTON') {
                    button = button.closest('button');
                }
                if (!button) return;
                
                var mode = button.getAttribute('data-mode');
                var researcherId = button.getAttribute('data-researcher-id');
                var researcherName = button.getAttribute('data-researcher-name');
                var pubtaId = button.getAttribute('data-pubta-id');
                var pubwMain = button.getAttribute('data-pubw-main');
                var pubwBud = button.getAttribute('data-pubw-bud');
                
                console.log('Modal triggered:', { mode, researcherId, researcherName, pubwMain, pubwBud });
                
                var formTitle = document.getElementById('authorFormTitle');
                var formMethod = document.getElementById('authorFormMethod');
                var btnCancel = document.getElementById('btnCancelEdit');
                var authorSelect = document.getElementById('authorSelect');
                var authorNameDisplay = document.getElementById('authorNameDisplay');
                var authorTypeSelect = document.getElementById('authorTypeSelect');
                var checkMain = document.getElementById('checkMain');
                var checkBud = document.getElementById('checkBud');
                var authorForm = document.getElementById('authorForm');
                
                if (mode === 'edit') {
                    // Update UI to Edit Mode
                    formTitle.innerHTML = '<i class="bi bi-pencil-square"></i> แก้ไขข้อมูล: ' + researcherName;
                    formMethod.value = 'PUT';
                    btnCancel.style.display = 'inline-block';
                    
                    // Hide TomSelect dropdown, Show Name Display
                    var tsWrapper = authorSelect.closest('.ts-wrapper') || authorSelect.parentElement.querySelector('.ts-wrapper');
                    if (tsWrapper) {
                        tsWrapper.style.display = 'none';
                    } else {
                        authorSelect.style.display = 'none';
                    }
                    authorNameDisplay.classList.remove('d-none');
                    authorNameDisplay.value = researcherName;
                    authorSelect.removeAttribute('required');
                    
                    // Set Form Values
                    authorTypeSelect.value = pubtaId;
                    checkMain.checked = (pubwMain == 1 || pubwMain === '1');
                    checkBud.checked = (pubwBud == 1 || pubwBud === '1');
                    
                    // Enable/Disable checkboxes based on ownership
                    // In Edit mode: enable if current author owns the flag OR no one has it
                    checkMain.disabled = hasMainOffice && (mainOfficeResearcherId != researcherId);
                    checkBud.disabled = hasBudgetSupport && (budgetSupportResearcherId != researcherId);
                    
                    // Disable taken single-only author types (unless current author owns it)
                    disableAuthorTypeOptions(parseInt(pubtaId));
                    
                    // Update Form Action
                    var actionUrl = "{{ route('backend.rdb_published.update_author', ['id' => $item->id, 'researcher_id' => ':id']) }}";
                    authorForm.setAttribute('action', actionUrl.replace(':id', researcherId));
                } else {
                    // Reset to Add Mode
                    resetAuthorForm();
                }
            });
        }
    });
    
    // Disable single-only author type options if already taken
    // currentPubtaId: the author type owned by current author (for Edit mode), or null (for Add mode)
    function disableAuthorTypeOptions(currentPubtaId) {
        var authorTypeSelect = document.getElementById('authorTypeSelect');
        if (!authorTypeSelect) return;
        
        for (var i = 0; i < authorTypeSelect.options.length; i++) {
            var option = authorTypeSelect.options[i];
            var optionValue = parseInt(option.value);
            
            // Check if this type is single-only AND already taken
            var isSingleOnly = singleOnlyTypes.indexOf(optionValue) !== -1;
            var isTaken = takenAuthorTypes.indexOf(optionValue) !== -1;
            var isOwnedByCurrent = (currentPubtaId === optionValue);
            
            // Disable if: single-only AND taken AND not owned by current author
            option.disabled = isSingleOnly && isTaken && !isOwnedByCurrent;
        }
    }

    // Reset Form (Switch to Add Mode)
    function resetAuthorForm() {
        var formTitle = document.getElementById('authorFormTitle');
        var formMethod = document.getElementById('authorFormMethod');
        var btnCancel = document.getElementById('btnCancelEdit');
        var authorSelect = document.getElementById('authorSelect');
        var authorNameDisplay = document.getElementById('authorNameDisplay');
        var authorTypeSelect = document.getElementById('authorTypeSelect');
        var checkMain = document.getElementById('checkMain');
        var checkBud = document.getElementById('checkBud');
        var authorForm = document.getElementById('authorForm');
        
        formTitle.innerHTML = '<i class="bi bi-person-plus"></i> เพิ่มผู้แต่งใหม่ (Add New)';
        formMethod.value = 'POST';
        btnCancel.style.display = 'none';
        
        // Show TomSelect dropdown, Hide Name Display
        var tsWrapper = authorSelect.closest('.ts-wrapper') || authorSelect.parentElement.querySelector('.ts-wrapper');
        if (tsWrapper) {
            tsWrapper.style.display = '';
        } else {
            authorSelect.style.display = '';
        }
        // Clear TomSelect selection
        if (window.authorTomSelect) {
            window.authorTomSelect.clear();
        }
        authorNameDisplay.classList.add('d-none');
        authorNameDisplay.value = '';
        authorSelect.setAttribute('required', 'required');
        
        // Reset Inputs
        checkMain.checked = false;
        checkBud.checked = false;
        
        // Disable checkboxes if already assigned to another author
        checkMain.disabled = hasMainOffice;
        checkBud.disabled = hasBudgetSupport;
        
        // First disable single-only author types that are already taken
        disableAuthorTypeOptions(null);
        
        // Then select the first non-disabled option
        for (var i = 0; i < authorTypeSelect.options.length; i++) {
            if (!authorTypeSelect.options[i].disabled) {
                authorTypeSelect.value = authorTypeSelect.options[i].value;
                break;
            }
        }
        
        // Reset Form Action
        authorForm.setAttribute('action', "{{ route('backend.rdb_published.store_author', $item->id) }}");
    }
</script>
@endpush
