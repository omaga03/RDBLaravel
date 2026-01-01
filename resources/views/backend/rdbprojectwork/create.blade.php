@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="เพิ่มข้อมูลงานวิจัยใหม่ (ผู้ร่วมโครงการ)" 
        icon="bi-plus-circle"
        mode="create" 
        action="{{ route('backend.rdbprojectwork.store') }}"
    >
        @include('backend.rdbprojectwork._form')

        <div class="mt-4 border-top pt-3">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
            <a href="{{ route('backend.rdbprojectwork.index') }}" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> ยกเลิก
            </a>
        </div>
    </x-form-wrapper>
</div>
@endsection