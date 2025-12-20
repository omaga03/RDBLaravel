@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-award me-2"></i> รายละเอียดทรัพย์สินทางปัญญา</h1>
        <a href="{{ route('backend.rdb_dip.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ $item->dip_request_number }}</h6>
            <div class="dropdown no-arrow">
                 <a href="{{ route('backend.rdb_dip.edit', $item->id) }}" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil-square"></i> แก้ไข
                </a>
                <form action="{{ route('backend.rdb_dip.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('ยืนยันการลบข้อมูล?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> ลบ
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">เลขที่คำขอ:</div>
                <div class="col-md-9">{{ $item->dip_request_number }}</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">ชื่อผลงาน:</div>
                <div class="col-md-9">
                    {{ $item->dip_data2_name ?? ($item->dip_name ?? '-') }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">ประเภท:</div>
                <div class="col-md-9">
                     {{ $item->dipType->diptype_name ?? '-' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">วันที่ยื่นคำขอ:</div>
                <div class="col-md-9">{{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">เลขที่ทะเบียน:</div>
                <div class="col-md-9">{{ $item->dip_number ?? '-' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">นักวิจัย/ผู้ประดิษฐ์:</div>
                <div class="col-md-9">
                    @if($item->researcher)
                        {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                    @else
                        -
                    @endif
                </div>
            </div>

            @if($item->pro_id)
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">โครงการที่เกี่ยวข้อง:</div>
                <div class="col-md-9">
                    @if($item->project)
                        <a href="{{ route('backend.rdb_project.show', $item->pro_id) }}" target="_blank">{{ $item->project->pro_nameTH }}</a>
                    @else
                        -
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
