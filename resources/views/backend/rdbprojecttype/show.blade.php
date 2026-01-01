@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดประเภททุน"
        icon="bi-wallet2"
        :backRoute="route('backend.rdbprojecttype.index')"
        :editRoute="route('backend.rdbprojecttype.edit', $item->pt_id)"
        :deleteRoute="route('backend.rdbprojecttype.destroy', $item->pt_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        {{-- Main Info --}}
        <div class="col-lg-8">
            <x-card title="ข้อมูลประเภททุน" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 30%;">รหัส (ID):</th>
                        <td><code>{{ $item->pt_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อประเภททุน:</th>
                        <td class="fw-bold fs-5 text-primary">{{ $item->pt_name }}</td>
                    </tr>
                    <tr>
                        <th>ปีงบประมาณ:</th>
                        <td>
                            @if($item->year)
                                <span class="badge bg-danger">{{ $item->year->year_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>กลุ่มประเภทงบประมาณ:</th>
                        <td>{{ $item->projectTypeGroup->pttg_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ทุนสนับสนุนสำหรับ:</th>
                        <td>{{ $forList[$item->pt_for] ?? ($item->pt_for ?? '-') }}</td>
                    </tr>
                    <tr>
                        <th>ทุนสนับสนุนสร้างโดย:</th>
                        <td>{{ $createList[$item->pt_created] ?? ($item->pt_created ?? '-') }}</td>
                    </tr>
                    <tr>
                        <th>คำนวณงบประมาณ:</th>
                        <td>
                            @if($item->pt_type == '1')
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> คำนวณ</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>การนำไปใช้ประโยชน์ (QA):</th>
                        <td>
                            @if($item->pt_utz == '1')
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> คำนวณ</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @if($item->pt_note)
                    <tr>
                        <th>รายละเอียดเพิ่มเติม:</th>
                        <td>{{ $item->pt_note }}</td>
                    </tr>
                    @endif
                </table>
            </x-card>
        </div>

        {{-- System Info --}}
        <div class="col-lg-4">
            @php
                $createdBy = $item->user_created ? \App\Models\User::find($item->user_created) : null;
                $updatedBy = $item->user_updated ? \App\Models\User::find($item->user_updated) : null;
                $createdByName = $createdBy?->researcher ? ($createdBy->researcher->researcher_fname . ' ' . $createdBy->researcher->researcher_lname) : ($createdBy?->username ?? '-');
                $updatedByName = $updatedBy?->researcher ? ($updatedBy->researcher->researcher_fname . ' ' . $updatedBy->researcher->researcher_lname) : ($updatedBy?->username ?? '-');
            @endphp
            <x-system-info :created_at="$item->created_at" :created_by="$createdByName" :updated_at="$item->updated_at" :updated_by="$updatedByName" />
        </div>
    </div>

    <x-action-buttons 
        :backRoute="route('backend.rdbprojecttype.index')"
        :editRoute="route('backend.rdbprojecttype.edit', $item->pt_id)"
        :deleteRoute="route('backend.rdbprojecttype.destroy', $item->pt_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
</div>
@endsection