@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดประเภทผลงานตีพิมพ์"
        icon="bi-journal-text"
        :backRoute="route('backend.rdbpublishedtype.index')"
        :editRoute="route('backend.rdbpublishedtype.edit', $item->pubtype_id)"
        :deleteRoute="route('backend.rdbpublishedtype.destroy', $item->pubtype_id)"
        :showPrint="true"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-card title="ข้อมูลประเภท" icon="bi-info-circle" color="blue">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัสประเภท:</th>
                        <td><code>{{ $item->pubtype_id }}</code></td>
                    </tr>
                    <tr>
                        <th>กลุ่มประเภท (Group):</th>
                        <td class="fw-bold fs-5">{{ $item->pubtype_group }}</td>
                    </tr>
                    <tr>
                        <th>ประเภทย่อย (Group Type):</th>
                        <td>{{ $item->pubtype_grouptype ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>กลุ่มย่อย (Subgroup):</th>
                        <td>{{ $item->pubtype_subgroup ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>คะแนนมาตรฐาน:</th>
                        <td>
                            @if($item->pubtype_score)
                                <span class="badge bg-success fs-6">{{ $item->pubtype_score }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </x-card>
        </div>

        <div class="col-lg-6">
            <x-system-info :created_at="$item->created_at" :updated_at="$item->updated_at" />
        </div>
    </div>

    <x-action-buttons 
        :backRoute="route('backend.rdbpublishedtype.index')"
        :editRoute="route('backend.rdbpublishedtype.edit', $item->pubtype_id)"
        :deleteRoute="route('backend.rdbpublishedtype.destroy', $item->pubtype_id)"
        :showPrint="true"
    />
</div>
@endsection
