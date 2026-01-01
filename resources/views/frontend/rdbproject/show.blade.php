@extends('layouts.app')

@section('content')
<div class="py-4">
    @php
        if (!function_exists('getFrontendUserName')) {
            function getFrontendUserName($user) {
                if(!$user) return '-';
                if($user->researcher) {
                    $r = $user->researcher;
                    return $r->researcher_fname . ' ' . $r->researcher_lname;
                }
                return $user->username ?? $user->email ?? '-';
            }
        }
    @endphp
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-folder2-open me-2"></i>รายละเอียดโครงการวิจัย</h4>
        <div>
            <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{-- General Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป (General Information)</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{!! $item->pro_nameTH !!}</h4>
                    @if($item->pro_nameEN)
                        <h5 class="text-muted mb-4">{!! $item->pro_nameEN !!}</h5>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12">
                            @if($item->pro_code)
                                <span class="badge bg-secondary me-2">รหัส: {{ $item->pro_code }}</span>
                            @endif
                            @if($item->year)
                                <span class="badge bg-info text-dark me-2">ปีงบประมาณ: {{ $item->year->year_name }}</span>
                            @endif
                            @if($item->status)
                                <span class="badge" style="background-color: {{ $item->status->ps_color ?? '#6c757d' }}; color: #fff;">สถานะ: {{ $item->status->ps_name }}</span>
                            @endif
                        </div>

                        <div class="col-md-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2">รายละเอียดเพิ่มเติม</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th style="width: 30%;">ประเภททุน:</th>
                                    <td>
                                        {{ $item->type->pt_name ?? '-' }}
                                        @if($item->typeSub)
                                            <small class="text-muted">({{ $item->typeSub->pts_name }})</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>งบประมาณ:</th>
                                    <td class="text-success fw-bold">{{ number_format($item->pro_budget, 2) }} บาท</td>
                                </tr>
                                <tr>
                                    <th>ระยะเวลา:</th>
                                    <td>
                                        {{ \App\Helpers\ThaiDateHelper::format($item->pro_date_start) }}
                                        ถึง
                                        {{ \App\Helpers\ThaiDateHelper::format($item->pro_date_end) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>หน่วยงาน:</th>
                                    <td>{{ $item->department->department_nameTH ?? '-' }}</td>
                                </tr>
                                @if($item->strategic)
                                <tr>
                                    <th>ยุทธศาสตร์:</th>
                                    <td>{{ $item->strategic->strategic_name ?? '-' }}</td>
                                </tr>
                                @endif
                                @if($item->pro_keyword)
                                <tr>
                                    <th>คำสำคัญ:</th>
                                    <td>
                                        @foreach(explode(',', $item->pro_keyword) as $keyword)
                                            <span class="badge bg-secondary me-1 mb-1">{{ trim($keyword) }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>จำนวนการเข้าดู:</th>
                                    <td><span class="badge bg-secondary">{{ number_format($item->pro_count_page ?? 0) }}</span> ครั้ง</td>
                                </tr>
                            </table>
                        </div>

                        @if($item->pro_abstract)
                        <div class="col-md-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2">บทคัดย่อ (Abstract)</h6>
                            @php
                                $separator = '<br><br><br><br>';
                                if (!str_contains($item->pro_abstract, $separator) && str_contains($item->pro_abstract, '<br><br><br>')) {
                                    $separator = '<br><br><br>';
                                }
                                $abstractParts = explode($separator, $item->pro_abstract);
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
                            </style>

                            @if(!empty($abstractParts[0]))
                            <div class="mb-3">
                                <span class="badge bg-primary mb-2">บทคัดย่อภาษาไทย</span>
                                <div class="bg-abstract p-3 rounded border" style="min-height: 100px; height: auto; overflow: visible; overflow-wrap: break-word; word-wrap: break-word;">
                                    {!! $abstractParts[0] !!}
                                </div>
                            </div>
                            @endif

                            @if(!empty($abstractParts[1]))
                            <div>
                                <span class="badge bg-info text-dark mb-2">บทคัดย่อภาษาอังกฤษ</span>
                                <div class="bg-abstract p-3 rounded border" style="min-height: 100px; height: auto; overflow: visible; overflow-wrap: break-word; word-wrap: break-word;">
                                    {!! $abstractParts[1] !!}
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Researchers --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> คณะผู้วิจัย (Researchers)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่งในโครงการ</th>
                                    <th>สัดส่วน (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->rdbProjectWorks as $work)
                                <tr>
                                    <td>
                                        @if($work->researcher)
                                            {{ $work->researcher->prefix->prefix_nameTH ?? '' }}{{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                            <small class="text-muted d-block">
                                                {{ $work->researcher->department->department_nameTH ?? $work->researcher->department->department_nameEN ?? $work->researcher->researcher_note ?? '-' }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ $work->position->position_nameTH ?? '-' }}
                                    </td>
                                    <td>{{ $work->ratio }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูลนักวิจัย</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">รวมสัดส่วน (Total Ratio):</td>
                                    <td>{{ $item->rdbProjectWorks->sum('ratio') }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    {{-- Files Card --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-down"></i> ไฟล์เอกสาร (Files)</h5>
                        </div>
                        <div class="card-body">
                            {{-- Abstract File - only if visible --}}
                            @if($item->pro_abstract_file)
                                <div class="d-flex align-items-center p-2 rounded mb-2 bg-body-tertiary border">
                                    <i class="bi bi-file-earmark-text text-secondary fs-4 me-2"></i>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('frontend.rdbproject.view_abstract', $item->pro_id) }}" target="_blank" class="text-decoration-none text-primary fw-bold hover-underline">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> ไฟล์บทคัดย่อ
                                        </a>
                                    </div>
                                    <div class="text-secondary" title="จำนวนเปิดดู">
                                        <i class="bi bi-eye"></i> {{ $item->pro_count_abs ?? 0 }}
                                    </div>
                                </div>
                            @else
                                <div class="d-flex align-items-center p-2 rounded mb-2 bg-body-tertiary border">
                                    <i class="bi bi-file-earmark text-muted fs-4 me-2"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted">ไม่มีไฟล์บทคัดย่อ</small>
                                    </div>
                                </div>
                            @endif

                            {{-- Full Report - only if visible --}}
                            @if($item->pro_file && $item->pro_file_show)
                                <div class="d-flex align-items-center p-2 rounded" style="background: rgba(25, 135, 84, 0.1);">
                                    <i class="bi bi-file-earmark-richtext text-success fs-4 me-2"></i>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('frontend.rdbproject.view_report', $item->pro_id) }}" target="_blank" class="text-decoration-none text-success fw-bold hover-underline">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> <strong>รายงานฉบับสมบูรณ์</strong><br>
                                            <small class="text-body-secondary ms-4">Full Report</small>
                                        </a>
                                    </div>
                                    <div class="text-success" title="จำนวนดาวน์โหลด">
                                        <i class="bi bi-eye"></i> {{ $item->pro_count_full ?? 0 }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    {{-- Additional Documents - only visible ones --}}
                    @php
                        $visibleFiles = $item->files->where('rf_files_show', 1);
                    @endphp
                    @if($visibleFiles->count() > 0)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-paperclip"></i> ข้อมูลเอกสารเพิ่มเติม</h5>
                        </div>
                        <div class="card-body">
                            @foreach($visibleFiles as $file)
                                <div class="d-flex align-items-center p-2 rounded mb-2 bg-body-tertiary border">
                                    <i class="bi bi-file-earmark-text text-secondary fs-4 me-2"></i>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('frontend.rdbproject.file.download', [$item->pro_id, $file->id]) }}" target="_blank" class="text-decoration-none text-primary fw-bold hover-underline">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $file->rf_filesname }}
                                        </a>
                                        @if($file->rf_note)
                                            <small class="text-muted d-block"><i class="bi bi-chat-left-text ms-4"></i> {{ $file->rf_note }}</small>
                                        @endif
                                    </div>
                                    <div class="text-secondary" title="จำนวนดาวน์โหลด">
                                        <i class="bi bi-eye"></i> {{ $file->rf_download ?? 0 }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-12">
                    {{-- History --}}
                    <div class="card shadow-sm mb-4 border-light">
                        <div class="card-header bg-light border-bottom-0">
                            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-clock-history"></i> ประวัติการแก้ไข (History)</h6>
                        </div>
                        <div class="card-body small text-secondary">
                            <p class="mb-2"><strong>สร้างเมื่อ:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($item->created_at) }} โดย {{ getFrontendUserName($item->createdBy) }}</p>
                            <p class="mb-0"><strong>แก้ไขล่าสุด:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($item->updated_at) }} โดย {{ getFrontendUserName($item->updatedBy) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
            <div class="row g-3 mb-4">
                @if($item->utilizations->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbprojectutilize.index', ['pro_id' => $item->pro_id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-success border-opacity-25 shadow-sm">
                            <div class="card-body text-center p-3">
                                <i class="bi bi-graph-up-arrow fs-1 text-success mb-2"></i>
                                <h6 class="text-body fw-bold mb-1">การนำไปใช้ประโยชน์</h6>
                                <span class="badge bg-success rounded-pill">{{ $item->utilizations->count() }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                
                @if($item->publisheds->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbpublished.index', ['pro_id' => $item->pro_id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-info border-opacity-25 shadow-sm">
                            <div class="card-body text-center p-3">
                                <i class="bi bi-journal-text fs-1 text-info mb-2"></i>
                                <h6 class="text-body fw-bold mb-1">ตีพิมพ์เผยแพร่</h6>
                                <span class="badge bg-info text-dark rounded-pill">{{ $item->publisheds->count() }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                @endif

                @if($item->dips->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbdip.index', ['pro_id' => $item->pro_id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-warning border-opacity-25 shadow-sm">
                            <div class="card-body text-center p-3">
                                <i class="bi bi-award fs-1 text-warning mb-2"></i>
                                <h6 class="text-body fw-bold mb-1">ทรัพย์สินทางปัญญา</h6>
                                <span class="badge bg-warning text-dark rounded-pill">{{ $item->dips->count() }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>

            {{-- Back Button --}}
            <div class="d-flex justify-content-start gap-2 mb-4">
                <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="bi bi-printer me-2"></i> พิมพ์
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    .hover-underline:hover {
        text-decoration: underline !important;
    }
</style>
@endsection