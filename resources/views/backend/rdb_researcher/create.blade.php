@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="เพิ่มข้อมูลนักวิจัยใหม่" 
        icon="bi-plus-circle"
        mode="create" 
        action="{{ route('backend.rdb_researcher.store') }}" 
        enctype="multipart/form-data"
    >
        @include('backend.rdb_researcher._form')
        
        <div class="mt-4 border-top pt-3">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
            <a href="{{ route('backend.rdb_researcher.index') }}" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> ยกเลิก
            </a>
        </div>
    </x-form-wrapper>
</div>
@endsection
