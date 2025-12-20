@extends('layouts.app')

@section('content')
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
    [data-bs-theme="dark"] .text-gray-800 { color: #e9ecef !important; }
</style>

<div class="py-4">
    <div class="row">
        <!-- Header & Actions -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0 text-gray-800"><i class="bi bi-journal-check"></i> รายละเอียดการตีพิมพ์ (Publication Details)</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-secondary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </a>
                    <a href="{{ route('backend.rdb_published.edit', $item->id) }}" class="btn btn-warning d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
                    </a>
                    <button onclick="window.print()" class="btn btn-primary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-printer me-2"></i> พิมพ์
                    </button>
                    <button type="submit" form="delete-form-top" class="btn btn-danger d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-trash me-2"></i> ลบ
                    </button>
                    <form id="delete-form-top" action="{{ route('backend.rdb_published.destroy', $item->id) }}" method="POST" class="d-none" onsubmit="return confirm('ยืนยันลบข้อมูลนี้?');">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 order-1 order-lg-1">
            <!-- General Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-print-none">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป (General Information)</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{{ $item->pub_name }}</h4>
                    @if($item->pub_name_journal)
                        <h5 class="text-muted mb-4"><i class="bi bi-journal-bookmark"></i> {{ $item->pub_name_journal }}</h5>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12">
                            @if($item->year)
                                <span class="badge bg-secondary me-2">ปี: {{ $item->year->year_name }}</span>
                            @endif
                            @if($item->pubtype)
                                <span class="badge bg-info text-dark me-2">{{ $item->pubtype->pubtype_name ?? $item->pubtype->pubtype_group }}</span>
                            @endif
                             @if($item->pub_score)
                                <span class="badge bg-success me-2">คะแนน: {{ $item->pub_score }}</span>
                            @endif
                        </div>

                        <div class="col-md-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2">รายละเอียดเพิ่มเติม</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th style="width: 30%;">ประเภทผลงาน:</th>
                                    <td>
                                        {{ $item->pubtype->pubtype_group ?? '-' }}
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                        {{ $item->pubtype->pubtype_grouptype ?? '-' }}
                                        @if($item->pubtype->pubtype_subgroup)
                                            <i class="bi bi-chevron-right text-muted small"></i>
                                            {{ $item->pubtype->pubtype_subgroup }}
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
                                @if($item->project)
                                <tr>
                                    <th>โครงการที่เกี่ยวข้อง:</th>
                                    <td>
                                        <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="text-decoration-none">
                                            <i class="bi bi-folder-symlink"></i> {{ $item->project->pro_nameTH }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                @if($item->pub_keyword)
                                <tr>
                                    <th>คำสำคัญ (Keywords):</th>
                                    <td>{{ $item->pub_keyword }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        @if($item->pub_abstract)
                        <div class="col-md-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2">บทคัดย่อ (Abstract)</h6>
                            <div class="p-3 bg-light rounded border" style="min-height: 100px;">
                                {!! $item->pub_abstract !!}
                            </div>
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
                <div class="card-header bg-success text-white d-print-none">
                    <h5 class="mb-0"><i class="bi bi-people"></i> ผู้แต่ง/นักวิจัย (Authors)</h5>
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
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->authors as $index => $author)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold">
                                             {{ $author->researcher_fname }} {{ $author->researcher_lname }}
                                        </span>
                                        <small class="text-muted d-block">
                                            {{ $author->department->department_nameTH ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if(isset($authorTypes[$author->pivot->pubta_id]))
                                            <span class="badge bg-info text-dark">{{ $authorTypes[$author->pivot->pubta_id] }}</span>
                                        @else
                                            -
                                        @endif
                                        @if($author->pivot->pubw_main == 1)
                                            <span class="badge bg-primary ms-1">Corresponding/Main</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูลผู้แต่ง</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Buttons (Print View Only) -->
             <div class="d-flex justify-content-end gap-2 mb-4 d-print-none">
                 <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                    <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                </a>
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
                        <h6 class="text-break mb-3">{{ $item->pub_file }}</h6>
                        <a href="{{ asset('storage/uploads/rdb_published/' . $item->pub_file) }}" target="_blank" class="btn btn-primary w-100">
                            <i class="bi bi-download"></i> ดาวน์โหลดไฟล์
                        </a>
                    @else
                        <div class="text-muted py-3">
                            <i class="bi bi-file-earmark-x" style="font-size: 2rem;"></i><br>
                            ไม่มีไฟล์แนบ
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="card shadow-sm d-print-none">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> ข้อมูลระบบ (System Info)</h5>
                </div>
                <div class="card-body small">
                     @php
                        function getUserName($id) {
                            $user = \App\Models\User::find($id);
                            if(!$user) return '-';
                            if($user->researcher) {
                                return $user->researcher->researcher_fname . ' ' . $user->researcher->researcher_lname;
                            }
                            return $user->username ?? '-';
                        }
                    @endphp
                    <p class="mb-2"><strong>สร้างเมื่อ:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($item->created_at ?? now()) }}</p>
                    <p class="mb-2"><strong>โดย:</strong> {{ getUserName($item->user_created) }}</p>
                    <hr>
                    <p class="mb-2"><strong>แก้ไขล่าสุด:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($item->updated_at ?? now()) }}</p>
                    <p class="mb-0"><strong>โดย:</strong> {{ getUserName($item->user_updated) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
