@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h4>รายละเอียดทรัพย์สินทางปัญญา</h4>
        </div>
        <div class="card-body">
            <!-- IP Info & Project -->
            <div class="row g-4 mb-4">
                <div class="col-md-7">
                    <div class="p-3 border rounded h-100 bg-body-tertiary">
                        <h5 class="mb-3 text-primary border-bottom pb-2"><i class="bi bi-award-fill me-2"></i>ข้อมูลทรัพย์สินทางปัญญา (IP Info)</h5>
                        
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ชื่อผลงาน:</div>
                            <div class="col-sm-8 fw-bold text-primary">{{ $item->dip_name ?: ($item->dip_data2_name ?? '-') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ประเภท:</div>
                            <div class="col-sm-8">
                                @if($item->dipType)
                                    <span class="badge bg-secondary">{{ $item->dipType->dipt_name }}</span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <!-- Request Info -->
                        @if($item->dip_request_number || $item->dip_request_date)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">คำขอ:</div>
                            <div class="col-sm-8">
                                @if($item->dip_request_number)
                                    <div>เลขที่: {{ $item->dip_request_number }}</div>
                                @endif
                                @if($item->dip_request_date)
                                    <div>วันที่: {{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}</div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Publication Info -->
                        @if($item->dip_publication_no || $item->dip_publication_date)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ประกาศโฆษณา:</div>
                            <div class="col-sm-8">
                                @if($item->dip_publication_no)
                                    <div>เลขที่: {{ $item->dip_publication_no }}</div>
                                @endif
                                @if($item->dip_publication_date)
                                    <div>วันที่: {{ \App\Helpers\ThaiDateHelper::format($item->dip_publication_date, false, true) }}</div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Patent/Registration Info -->
                        @if($item->dip_patent_number || $item->dip_department_no)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">เลขที่สิทธิบัตร/ทะเบียน:</div>
                            <div class="col-sm-8 font-monospace text-success fw-bold">
                                {{ $item->dip_patent_number ?? $item->dip_department_no }}
                            </div>
                        </div>
                        @endif

                        @if($item->dip_startdate || $item->dip_enddate)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ระยะเวลาการคุ้มครอง:</div>
                            <div class="col-sm-8">
                                @if($item->dip_startdate)
                                    {{ \App\Helpers\ThaiDateHelper::format($item->dip_startdate, false, true) }}
                                @endif
                                @if($item->dip_enddate)
                                    - {{ \App\Helpers\ThaiDateHelper::format($item->dip_enddate, false, true) }}
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Owner info -->
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ผู้ทรงสิทธิ/ผู้ประดิษฐ์:</div>
                            <div class="col-sm-8">
                                @if($item->researcher)
                                    <div>
                                        <i class="bi bi-person-fill"></i>
                                        {{ $item->researcher->prefix->prefix_nameTH ?? '' }}{{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                        @if($item->researcher->department)
                                            <div class="text-muted ms-4 small"><i class="bi bi-building"></i> {{ $item->researcher->department->department_nameTH }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($item->dip_data2_status)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">สถานะ:</div>
                            <div class="col-sm-8"><span class="badge bg-info text-dark">{{ $item->dip_data2_status }}</span></div>
                        </div>
                        @endif

                    </div>
                </div>

                <!-- Project Info -->
                <div class="col-md-5">
                    <div class="card h-100 border-success border-opacity-25 shadow-sm">
                        <div class="card-header bg-success bg-opacity-10 text-success fw-bold">
                            <i class="bi bi-folder-fill me-2"></i>โครงการวิจัยที่เกี่ยวข้อง
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
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-folder-x display-6 d-block mb-2"></i>
                                    ไม่พบข้อมูลโครงการที่เกี่ยวข้อง
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail & Conclusion -->
            @if($item->dip_data2_conclusion || $item->dip_note)
            <div class="card mb-4 border-0 shadow-sm bg-transparent">
                <div class="card-header bg-transparent border-bottom-0 pt-3 ps-0">
                    <h5 class="fw-bold text-body"><i class="bi bi-file-text-fill me-2"></i>รายละเอียด / บทสรุป</h5>
                </div>
                <div class="card-body ps-0 pt-0">
                    @if($item->dip_data2_conclusion)
                    <div class="p-3 bg-body-tertiary border rounded mb-3">
                        <h6 class="fw-bold">บทสรุปการประดิษฐ์:</h6>
                        <p class="mb-0 text-break">{!! nl2br(e($item->dip_data2_conclusion)) !!}</p>
                    </div>
                    @endif
                    
                    @if($item->dip_note)
                    <div class="p-3 bg-body-tertiary border rounded">
                        <h6 class="fw-bold">หมายเหตุ:</h6>
                        <p class="mb-0 text-break">{!! nl2br(e($item->dip_note)) !!}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Files -->
            @php
                $files = [
                    ['name' => 'ไฟล์แนบหลัก', 'path' => $item->dip_files],
                    ['name' => 'ไฟล์ข้อมูล 1 (Date Start)', 'path' => $item->dip_data1_files],
                    ['name' => 'ไฟล์ข้อมูล 2 (Conclusion)', 'path' => $item->dip_data2_files_con],
                    ['name' => 'ไฟล์แบบแสดง (Drawing)', 'path' => $item->dip_data3_drawing_picture],
                    ['name' => 'ไฟล์แบบฟอร์มคำขอ', 'path' => $item->dip_data_forms_request],
                ];
                $hasFiles = false;
                foreach($files as $f) { if($f['path']) $hasFiles = true; }
            @endphp

            @if($hasFiles)
            <div class="alert alert-secondary border shadow-sm">
                <h5 class="alert-heading fw-bold mb-3"><i class="bi bi-paperclip me-2"></i>เอกสารแนบ</h5>
                <div class="row g-2">
                    @foreach($files as $file)
                        @if($file['path'])
                        <div class="col-md-4 col-sm-6">
                            <a href="{{ asset('storage/' . $file['path']) }}" class="btn btn-light border w-100 text-start text-truncate" target="_blank">
                                <i class="bi bi-file-earmark-text text-danger me-2"></i>
                                {{ $file['name'] }}
                            </a>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection