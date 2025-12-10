@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h4>{{ $item->pro_nameTH }}</h4>
            @if($item->pro_nameEN)
                <h6 class="text-muted">{{ $item->pro_nameEN }}</h6>
            @endif
        </div>
        <div class="card-body">
            <!-- Project Info & Status -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="p-3 border rounded h-100 bg-body-tertiary">
                        <h5 class="mb-3 text-primary border-bottom pb-2"><i class="bi bi-info-circle-fill me-2"></i>Project Info</h5>
                        
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Year:</div>
                            <div class="col-sm-8">{{ $item->year->year_name ?? '-' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Type:</div>
                            <div class="col-sm-8">{{ $item->type->pt_name ?? '-' }}</div>
                        </div>
                        @if($item->pts)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Sub-Type:</div>
                            <div class="col-sm-8">
                                {{ $item->pts->pts_name }}
                                @if($item->pts->pts_file)
                                    <a href="{{ asset('storage/uploads/project_type/' . $item->pts->pts_file) }}" target="_blank" class="ms-2 text-danger">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Department:</div>
                            <div class="col-sm-8">{{ $item->department->department_nameTH ?? '-' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Budget:</div>
                            <div class="col-sm-8 text-success fw-bold">{{ number_format($item->pro_budget, 2) }} THB</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 fw-bold">Status:</div>
                            <div class="col-sm-8">
                                @if($item->status)
                                    <span class="badge" style="background-color: {{ $item->status->ps_color }}; color: white; font-size: 0.9em;">
                                        {{ $item->status->ps_name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Researchers -->
                <div class="col-md-4">
                    <div class="card h-100 border-primary border-opacity-25 shadow-sm">
                        <div class="card-header bg-primary bg-opacity-10 text-primary fw-bold">
                            <i class="bi bi-people-fill me-2"></i>Researchers
                        </div>
                        <div class="card-body">
                            @if($item->rdbProjectWorks->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($item->rdbProjectWorks as $work)
                                        @if($work->researcher)
                                            <li class="list-group-item bg-transparent px-0 py-2 border-bottom-0">
                                                <i class="bi bi-person-circle me-1 text-muted"></i>
                                                <a href="{{ route('frontend.rdbresearcher.show', $work->researcher->researcher_id) }}" class="text-decoration-none text-body fw-bold">
                                                    {{ $work->researcher->prefix->prefix_nameTH ?? '' }} 
                                                    {{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                                </a>
                                                <small class="text-muted d-block ms-4" style="font-size: 0.8em;">
                                                    @if($work->researcher->department)
                                                        {{ $work->researcher->department->department_nameTH }}
                                                    @elseif($work->researcher->researcher_note)
                                                        {{ $work->researcher->researcher_note }}
                                                    @else
                                                        -
                                                    @endif
                                                </small>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keywords -->
            @if($item->pro_keyword)
            <div class="mb-4">
                <div class="d-flex align-items-center mb-2">
                    <h5 class="fw-bold mb-0 text-body"><i class="bi bi-tags-fill me-2"></i>Keywords</h5>
                </div>
                <div class="p-3 rounded border bg-body-tertiary">
                    @foreach(explode(',', $item->pro_keyword) as $keyword)
                        <span class="badge bg-secondary me-1 mb-1 fs-6 fw-normal text-wrap">{{ trim($keyword) }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Abstract -->
            <div class="card mb-4 border-0 shadow-sm bg-transparent">
                <div class="card-header bg-transparent border-bottom-0 pt-3 ps-0">
                    <h5 class="fw-bold text-body"><i class="bi bi-file-text-fill me-2"></i>Content</h5>
                </div>
                <div class="card-body ps-0 pt-0">
                     @php
                        // Split abstract by 3 or more consecutive <br> tags
                        $abstracts = preg_split('/(<br\s*\/?>\s*){3,}/i', $item->pro_abstract);
                    @endphp

                    @if(count($abstracts) >= 2)
                        <div class="p-3 bg-body-tertiary border rounded mb-3">
                            <h6 class="fw-bold text-primary">บทคัดย่อ</h6>
                            <p class="text-break mb-0">{!! $abstracts[0] !!}</p>
                        </div>
                        
                        <div class="p-3 bg-body-secondary border rounded">
                            <h6 class="fw-bold text-primary">Abstract</h6>
                            <p class="text-break mb-0">{!! $abstracts[1] !!}</p>
                        </div>
                    @else
                        <div class="p-3 bg-body-tertiary border rounded">
                            <h6 class="fw-bold text-primary">Abstract</h6>
                            <p class="text-break mb-0">{!! $item->pro_abstract ?? 'No abstract available.' !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Downloads -->
            <div class="alert alert-secondary border shadow-sm d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <h5 class="alert-heading fw-bold mb-1"><i class="bi bi-download me-2"></i>Downloads</h5>
                    <p class="mb-0 small">Access full reports and documents for this project.</p>
                </div>
                <div class="mt-2 mt-md-0 d-flex gap-2">
                    @if($item->pro_abstract_file)
                        <a href="{{ asset('storage/uploads/projects/' . $item->pro_abstract_file) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Abstract
                        </a>
                    @else
                        <button class="btn btn-outline-secondary" disabled>No Abstract</button>
                    @endif

                    @if($item->pro_file)
                        <a href="{{ asset('storage/uploads/projects/' . $item->pro_file) }}" class="btn btn-primary shadow-sm" target="_blank">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Full Report
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled>No Full Report</button>
                    @endif
                </div>
            </div>

            <!-- Related Documents -->
            @if($item->files->where('rf_files_show', '<>', '0')->count() > 0)
            <div class="mt-4">
                <h5 class="fw-bold mb-3 text-body"><i class="bi bi-paperclip me-2"></i>Related Documents</h5>
                <ul class="list-group list-group-flush border rounded">
                    @foreach($item->files->where('rf_files_show', '<>', '0') as $file)
                        <li class="list-group-item bg-body-tertiary d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                {{ $file->rf_filesname }}
                                @if($file->rf_note)
                                    <small class="text-muted d-block ms-4">{{ $file->rf_note }}</small>
                                @endif
                            </div>
                            <a href="{{ asset('storage/uploads/projects/files/' . $file->rf_files) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif



            <!-- Research Outputs -->
            <div class="row mt-4 g-3">
                @if($item->utilizations->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbprojectutilize.index', ['pro_id' => $item->pro_id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-success border-opacity-25 shadow-sm hover-shadow transition">
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
                        <div class="card h-100 border-info border-opacity-25 shadow-sm hover-shadow transition">
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
                        <div class="card h-100 border-warning border-opacity-25 shadow-sm hover-shadow transition">
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

            <div class="mt-4">
                <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection