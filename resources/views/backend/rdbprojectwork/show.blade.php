@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header 
        title="รายละเอียดงานวิจัย (ผู้ร่วมโครงการ)" 
        icon="bi-people"
        :backRoute="route('backend.rdbprojectwork.index')"
        :editRoute="route('backend.rdbprojectwork.edit', $item->id)"
    />

    <div class="row">
        <div class="col-lg-8">
            <x-card title="ข้อมูลงานวิจัย" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 30%;">ID:</th>
                        <td><code>{{ $item->id ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <th>โครงการ:</th>
                        <td>
                            @if($item->project)
                                <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="fw-bold text-primary text-decoration-none" target="_blank">
                                    {{ $item->project->pro_nameTH }}
                                </a>
                                @if($item->project->pro_code)
                                    <div class="small text-muted">{{ $item->project->pro_code }}</div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>นักวิจัย:</th>
                        <td>
                            @if($item->researcher)
                                <div class="fw-bold">{{ $item->researcher->researcher_nameTH ?? $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>ตำแหน่ง:</th>
                        <td>{{ $item->position->position_nameTH ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>สัดส่วน:</th>
                        <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $item->ratio }}%</span></td>
                    </tr>
                </table>
            </x-card>
        </div>
    </div>
    
    <div class="mt-4">
         <x-action-buttons 
            :backRoute="route('backend.rdbprojectwork.index')"
            :editRoute="route('backend.rdbprojectwork.edit', $item->id)"
            :deleteRoute="route('backend.rdbprojectwork.destroy', $item->id)"
        />
    </div>
</div>
@endsection