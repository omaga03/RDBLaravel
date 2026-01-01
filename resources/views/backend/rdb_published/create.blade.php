@extends('layouts.app')

@section('content')
    <x-form-wrapper 
        title="เพิ่มข้อมูลการตีพิมพ์ใหม่" 
        icon="bi-pencil-square"
        mode="create" 
        :backRoute="route('backend.rdb_published.index')"
        :actionRoute="route('backend.rdb_published.store')"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_published._form')
    </x-form-wrapper>
@endsection
