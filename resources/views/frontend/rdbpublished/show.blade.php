@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h4>{!! $item->pub_name !!}</h4>
        </div>
        <div class="card-body">
            <!-- Publication Info & Journal -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="p-3 border rounded h-100 bg-body-tertiary">
                        <h5 class="mb-3 text-primary border-bottom pb-2"><i class="bi bi-info-circle-fill me-2"></i>Publication Info</h5>
                        
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Year:</div>
                            <div class="col-sm-8">{{ $item->year->year_name ?? '-' }}</div>
                        </div>
                        @if($item->branch)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">สาขาการตีพิมพ์:</div>
                            <div class="col-sm-8">{{ $item->branch->branch_name }}</div>
                        </div>
                        @endif
                        @if($item->pubtype)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">ประเภทผลงาน:</div>
                            <div class="col-sm-8">{{ $item->pubtype->pubtype_group }}</div>
                        </div>
                        @endif
                        @if($item->pubtype && $item->pubtype->pubtype_subgroup)
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Sub-Type:</div>
                            <div class="col-sm-8">{{ $item->pubtype->pubtype_subgroup }}</div>
                        </div>
                        @endif
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Date:</div>
                            <div class="col-sm-8">
                                @if($item->pub_date)
                                    @php
                                        $date = \Carbon\Carbon::parse($item->pub_date);
                                        $months = [
                                            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
                                            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
                                        ];
                                    @endphp
                                    {{ $date->day }} {{ $months[$date->month] }} {{ $date->year + 543 }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        @if($item->project)
                        <div class="row">
                            <div class="col-sm-4 fw-bold">Project:</div>
                            <div class="col-sm-8">
                                <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" class="text-decoration-none text-body">
                                    {!! $item->project->pro_nameTH !!}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Authors -->
                <div class="col-md-4">
                    <div class="card h-100 border-primary border-opacity-25 shadow-sm">
                        <div class="card-header bg-primary bg-opacity-10 text-primary fw-bold">
                            <i class="bi bi-people-fill me-2"></i>ผู้เขียนบทความ
                        </div>
                        <div class="card-body">
                            @if($item->authors && $item->authors->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($item->authors as $author)
                                        <li class="list-group-item bg-transparent px-0 py-2 border-bottom-0">
                                            <i class="bi bi-person-circle me-1 text-muted"></i>
                                            <a href="{{ route('frontend.rdbresearcher.show', $author->researcher_id) }}" class="text-decoration-none text-body fw-bold">
                                                {{ $author->prefix->prefix_nameTH ?? '' }} 
                                                {{ $author->researcher_fname }} {{ $author->researcher_lname }}
                                            </a>
                                            <span class="text-muted d-block ms-4" style="font-size: 0.85em;">
                                                @if(isset($author->authorType))
                                                    {{ $author->authorType->pubta_nameTH }} •
                                                @endif
                                                @if($author->department)
                                                    {{ $author->department->department_nameTH }}
                                                @elseif($author->researcher_note)
                                                    {{ $author->researcher_note }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journal (Highlighted) -->
            @if($item->pub_name_journal)
            <div class="alert alert-info border shadow-sm mb-4">
                <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-journal-text me-2"></i>แหล่งตีพิมพ์ (Journal)</h6>
                <p class="mb-0">{!! $item->pub_name_journal !!}</p>
            </div>
            @endif

            <!-- Keywords -->
            @if($item->pub_keyword)
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-tags-fill me-2"></i>คำสำคัญ (Keywords)</h6>
                <div class="p-3 rounded border bg-body-tertiary">
                    @foreach(explode(',', $item->pub_keyword) as $keyword)
                        <span class="badge bg-secondary me-1 mb-1 fs-6 fw-normal text-wrap">{{ trim($keyword) }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Abstract -->
            <div class="card mb-4 border-0 shadow-sm bg-transparent">
                <div class="card-header bg-transparent border-bottom-0 pt-3 ps-0">
                    <h5 class="fw-bold text-body"><i class="bi bi-file-text-fill me-2"></i>Abstract</h5>
                </div>
                <div class="card-body ps-0 pt-0">
                    @php
                        // Split abstract by 3 or more consecutive <br> tags
                        $abstracts = preg_split('/(<br\s*\/?>\\s*){3,}/i', $item->pub_abstract ?? '');
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
                            <h6 class="fw-bold text-primary">บทคัดย่อ / Abstract</h6>
                            <p class="text-break mb-0">{!! $item->pub_abstract ?? 'ไม่มีข้อมูลบทคัดย่อ' !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Downloads -->
            @if($item->pub_file)
            <div class="alert alert-secondary border shadow-sm d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <h5 class="alert-heading fw-bold mb-1"><i class="bi bi-download me-2"></i>Downloads</h5>
                    <p class="mb-0 small">Access full paper and documents for this publication.</p>
                </div>
                <div class="mt-2 mt-md-0 d-flex gap-2">
                    <a href="{{ asset('storage/uploads/published/' . $item->pub_file) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Full Paper
                    </a>
                </div>
            </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection