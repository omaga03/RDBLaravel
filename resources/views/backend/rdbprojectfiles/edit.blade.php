@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="แก้ไขข้อมูลไฟล์แนบ" 
        icon="bi-pencil-square"
        mode="edit" 
        :backRoute="route('backend.rdbprojectfiles.show', $item->id)"
        :actionRoute="route('backend.rdbprojectfiles.update', $item->id)"
        method="PUT"
    >
        @include('backend.rdbprojectfiles._form')
    </x-form-wrapper>
</div>
@endsection