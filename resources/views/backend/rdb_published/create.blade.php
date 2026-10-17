@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มข้อมูลการตีพิมพ์ใหม่" 
        icon="bi-journal-plus"
        mode="create" 
        :backRoute="route('backend.rdb_published.index')"
        :actionRoute="route('backend.rdb_published.store')"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_published._form')
    </x-form-wrapper>
</div>
@endsection
