@extends('layouts.app')

@section('content')
    <x-form-wrapper 
        title="เพิ่มไฟล์แนบใหม่" 
        icon="bi-file-earmark-plus"
        mode="create" 
        action="{{ route('backend.rdbprojectfiles.store') }}"
    >
        @include('backend.rdbprojectfiles._form')

        <div class="mt-4 border-top pt-3">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
            <a href="{{ route('backend.rdbprojectfiles.index') }}" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> ยกเลิก
            </a>
        </div>
    </x-form-wrapper>
@endsection