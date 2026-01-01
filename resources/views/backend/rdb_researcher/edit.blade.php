@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="แก้ไขข้อมูลนักวิจัย" 
        icon="bi-pencil-square"
        mode="edit" 
        action="{{ route('backend.rdb_researcher.update', $researcher->researcher_id) }}" 
        method="PUT"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_researcher._form')

        <div class="mt-4 border-top pt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกการแก้ไข</button>
            <a href="{{ route('backend.rdb_researcher.show', $researcher->researcher_id) }}" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> ยกเลิก
            </a>
        </div>
    </x-form-wrapper>
</div>
@endsection
