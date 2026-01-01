@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(isset($item->id))
        <x-form-wrapper 
            title="แก้ไขข้อมูลงานวิจัย" 
            icon="bi-pencil-square"
            mode="edit" 
            :backRoute="route('backend.rdbprojectwork.show', $item->id)"
            :actionRoute="route('backend.rdbprojectwork.update', $item->id)"
            method="PUT"
        >
            @include('backend.rdbprojectwork._form')
        </x-form-wrapper>
    @else
        <div class="alert alert-danger">ไม่สามารถแก้ไขข้อมูลได้ (ไม่พบ Primary Key)</div>
    @endif
</div>
@endsection