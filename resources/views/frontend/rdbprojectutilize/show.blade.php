@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h4>รายละเอียดการนำไปใช้ประโยชน์</h4>
        </div>
        <div class="card-body">
            <!-- Utilization Info & Project -->
            <div class="row g-4 mb-4">
                <div class="col-md-7">
                    <div class="p-3 border rounded h-100 bg-body-tertiary">
                        <h5 class="mb-3 text-primary border-bottom pb-2"><i class="bi bi-info-circle-fill me-2"></i>Utilization Info</h5>
                        
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">วันที่นำไปใช้:</div>
                            <div class="col-sm-8">
                                @if($item->utz_date)
                                    <div><i class="bi bi-calendar-check me-2 text-primary"></i> <strong>วันที่นำไปใช้:</strong> {{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}</div>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">หน่วยงาน:</div>
                            <div class="col-sm-8">{{ $item->utz_department_name ?? '-' }}</div>
                        </div>
                        @if($item->utz_department_address || $item->changwat)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ที่อยู่:</div>
                            <div class="col-sm-8">
                                @if($item->utz_department_address)
                                    {!! $item->utz_department_address !!}
                                @endif
                                @if($item->changwat)
                                    @if($item->changwat->tambon_t)
                                        {{ $item->changwat->tambon_t }}
                                    @endif
                                    @if($item->changwat->amphoe_t)
                                        {{ $item->changwat->amphoe_t }}
                                    @endif
                                    @if($item->changwat->changwat_t)
                                        {{ $item->changwat->changwat_t }}
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($item->utz_leading)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ผู้ลงนาม:</div>
                            <div class="col-sm-8">
                                {!! $item->utz_leading !!}
                                @if($item->utz_leading_position)
                                    ({{ $item->utz_leading_position }})
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($item->utz_budget)
                        <div class="row">
                            <div class="col-sm-4 fw-bold">งบประมาณ:</div>
                            <div class="col-sm-8 text-success fw-bold">{{ number_format($item->utz_budget, 2) }} บาท</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Project Info -->
                <div class="col-md-5">
                    <div class="card h-100 border-success border-opacity-25 shadow-sm">
                        <div class="card-header bg-success bg-opacity-10 text-success fw-bold">
                            <i class="bi bi-folder-fill me-2"></i>โครงการวิจัย
                        </div>
                        <div class="card-body">
                            @if($item->project)
                                <div class="mb-3">
                                    <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" class="text-decoration-none text-body fw-bold">
                                        @if($item->project->pro_code)
                                            <span class="text-primary">{!! $item->project->pro_code !!}</span>
                                        @endif
                                        {!! $item->project->pro_nameTH !!}
                                    </a>
                                </div>
                                <small class="text-muted d-block">
                                    @if($item->project->type)
                                        {{ $item->project->type->pt_name }}
                                        @if($item->project->typeSub)
                                            • {{ $item->project->typeSub->pts_name }}
                                        @endif
                                    @endif
                                </small>
                                @if($item->project->department)
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-building me-1"></i>{{ $item->project->department->department_nameTH }}
                                    </small>
                                @endif
                                @if($item->project->rdbProjectWorks && $item->project->rdbProjectWorks->count() > 0)
                                    <hr class="my-2">
                                    <small class="text-muted d-block">
                                        <strong>นักวิจัย:</strong>
                                    </small>
                                    @foreach($item->project->rdbProjectWorks as $work)
                                        @if($work->researcher)
                                            <small class="text-muted d-block ms-2">
                                                <i class="bi bi-person-circle me-1"></i>
                                                {{ $work->researcher->prefix->prefix_nameTH ?? '' }}
                                                {{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                            </small>
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail -->
            @if($item->utz_detail)
            <div class="card mb-4 border-0 shadow-sm bg-transparent">
                <div class="card-header bg-transparent border-bottom-0 pt-3 ps-0">
                    <h5 class="fw-bold text-body"><i class="bi bi-file-text-fill me-2"></i>รายละเอียดการนำไปใช้ประโยชน์</h5>
                </div>
                <div class="card-body ps-0 pt-0">
                    <div class="p-3 bg-body-tertiary border rounded">
                        <p class="mb-0 text-break">{!! $item->utz_detail !!}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Files -->
            @if($item->utz_files)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                    <h5 class="mb-0"><i class="bi bi-download me-2"></i>ไฟล์แนบ (Attachments)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text text-primary fs-4 me-3"></i>
                                <div>
                                    <a href="{{ asset('storage/' . $item->utz_files) }}" class="text-decoration-none fw-bold text-primary hover-underline" target="_blank">
                                        <i class="bi bi-box-arrow-up-right me-1 small"></i> เอกสารการนำไปใช้ประโยชน์
                                    </a>
                                    <small class="text-muted d-block">คลิกเพื่อดาวน์โหลด/เปิดไฟล์</small>
                                </div>
                            </div>
                            <div class="text-muted" title="จำนวนดาวน์โหลด">
                                <i class="bi bi-eye"></i> {{ $item->utz_countfile ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
                <button onclick="window.print()" class="btn btn-outline-secondary px-4"><i class="bi bi-printer me-1"></i> พิมพ์</button>
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