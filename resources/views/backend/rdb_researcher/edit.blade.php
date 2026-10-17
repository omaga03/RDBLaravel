@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="แก้ไขข้อมูลนักวิจัย" 
        icon="bi-pencil-square"
        mode="edit" 
        :actionRoute="route('backend.rdb_researcher.update', $researcher->researcher_id)" 
        :backRoute="route('backend.rdb_researcher.show', $researcher->researcher_id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_researcher._form')
    </x-form-wrapper>
</div>
@endsection
