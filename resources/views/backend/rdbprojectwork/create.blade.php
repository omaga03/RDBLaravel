@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มข้อมูลงานวิจัยใหม่ (ผู้ร่วมโครงการ)" 
        icon="bi-plus-circle"
        mode="create" 
        :actionRoute="route('backend.rdbprojectwork.store')"
        :backRoute="route('backend.rdbprojectwork.index')"
    >
        @include('backend.rdbprojectwork._form')
    </x-form-wrapper>
</div>
@endsection