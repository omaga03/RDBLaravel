@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มข้อมูลนักวิจัยใหม่" 
        icon="bi-person-plus"
        mode="create" 
        :actionRoute="route('backend.rdb_researcher.store')" 
        :backRoute="route('backend.rdb_researcher.index')"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_researcher._form')
    </x-form-wrapper>
</div>
@endsection
