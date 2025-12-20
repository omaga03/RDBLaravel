@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-rocket-takeoff me-2"></i> รายละเอียดการใช้ประโยชน์</h1>
        <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ $item->utz_department_name }}</h6>
            <div class="dropdown no-arrow">
                 <a href="{{ route('backend.rdbprojectutilize.edit', $item->utz_id) }}" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil-square"></i> แก้ไข
                </a>
                <form action="{{ route('backend.rdbprojectutilize.destroy', $item->utz_id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('ยืนยันการลบข้อมูล?');">
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
                <div class="col-md-3 font-weight-bold">หน่วยงานที่นำไปใช้:</div>
                <div class="col-md-9">{{ $item->utz_department_name }}</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">วันที่:</div>
                <div class="col-md-9">
                    {{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}
                </div>
            </div>

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

            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">ที่อยู่:</div>
                <div class="col-md-9">{{ $item->utz_department_address ?? '-' }}</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">จังหวัด/อำเภอ/ตำบล:</div>
                <div class="col-md-9">
                    @if($item->changwat)
                        {{ $item->changwat->tambon_t }} / {{ $item->changwat->amphoe_t }} / {{ $item->changwat->changwat_t }}
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">ผู้ลงนาม:</div>
                <div class="col-md-9">
                    {{ $item->utz_leading ?? '-' }} 
                    @if($item->utz_leading_position)
                        ({{ $item->utz_leading_position }})
                    @endif
                </div>
            </div>
            
             <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">หมายเหตุ:</div>
                <div class="col-md-9">{!! $item->utz_note ?? '-' !!}</div>
            </div>

        </div>
    </div>
</div>
@endsection
