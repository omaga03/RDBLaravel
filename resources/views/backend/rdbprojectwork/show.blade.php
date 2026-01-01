@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-page-header 
        title="รายละเอียดงานวิจัย (ผู้ร่วมโครงการ)" 
        icon="bi-people"
        :backRoute="route('backend.rdbprojectwork.index')"
        :editRoute="route('backend.rdbprojectwork.edit', $item->id)"
    />

    <div class="row justify-content-center">
        <div class="col-md-10">
            <x-card title="ข้อมูลงานวิจัย" icon="bi-info-circle" color="blue">
                <div class="row mb-3">
                    <label class="col-md-4 text-end text-muted fw-bold">ID:</label>
                    <div class="col-md-8">{{ $item->id ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-4 text-end text-muted fw-bold">โครงการ:</label>
                    <div class="col-md-8">
                        @if($item->project)
                            <div class="fw-bold text-primary">{{ $item->project->pro_nameTH }}</div>
                            <small class="text-muted">{{ $item->project->pro_code ?? '-' }}</small>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-4 text-end text-muted fw-bold">นักวิจัย:</label>
                    <div class="col-md-8">
                        @if($item->researcher)
                            <div class="fw-bold">{{ $item->researcher->researcher_nameTH ?? $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname }}</div>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-4 text-end text-muted fw-bold">ตำแหน่ง:</label>
                    <div class="col-md-8">{{ $item->position->position_nameTH ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-4 text-end text-muted fw-bold">สัดส่วน:</label>
                    <div class="col-md-8"><span class="badge bg-info text-dark">{{ $item->ratio }}%</span></div>
                </div>
            </x-card>
        </div>
    </div>
    
    <div class="row justify-content-center mt-3">
        <div class="col-md-10">
             <x-action-buttons 
                :backRoute="route('backend.rdbprojectwork.index')"
                :editRoute="route('backend.rdbprojectwork.edit', $item->id)"
                :deleteRoute="route('backend.rdbprojectwork.destroy', $item->id)"
            />
        </div>
    </div>
</div>
@endsection