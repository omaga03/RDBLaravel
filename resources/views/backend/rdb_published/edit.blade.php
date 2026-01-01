@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="แก้ไขข้อมูลการตีพิมพ์" 
        icon="bi-pencil-square"
        mode="edit" 
        :backRoute="route('backend.rdb_published.show', $item->id)"
        :actionRoute="route('backend.rdb_published.update', $item->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_published._form')
    </x-form-wrapper>
</div>
@endsection
