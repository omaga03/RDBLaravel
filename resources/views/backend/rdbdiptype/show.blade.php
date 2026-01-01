@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดประเภททรัพย์สินทางปัญญา"
        icon="bi-lightbulb"
        :backRoute="route('backend.rdbdiptype.index')"
        :editRoute="route('backend.rdbdiptype.edit', $item->dipt_id)"
        :deleteRoute="route('backend.rdbdiptype.destroy', $item->dipt_id)"
        :showPrint="true"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-card title="ข้อมูลประเภท" icon="bi-info-circle" color="blue">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัสประเภท:</th>
                        <td><code>{{ $item->dipt_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อประเภท:</th>
                        <td class="fw-bold fs-5">{{ $item->dipt_name }}</td>
                    </tr>
                </table>
            </x-card>
        </div>

        <div class="col-lg-6">
            <x-system-info :created_at="$item->created_at" :updated_at="$item->updated_at" />
        </div>
    </div>

    <x-action-buttons 
        :backRoute="route('backend.rdbdiptype.index')"
        :editRoute="route('backend.rdbdiptype.edit', $item->dipt_id)"
        :deleteRoute="route('backend.rdbdiptype.destroy', $item->dipt_id)"
        :showPrint="true"
    />
</div>
@endsection
