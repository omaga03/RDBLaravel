@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="แก้ไขประเภททุน" 
        icon="bi-pencil-square"
        mode="edit" 
        :actionRoute="route('backend.rdbprojecttype.update', $item->pt_id)"
        method="PUT"
        :backRoute="route('backend.rdbprojecttype.show', $item->pt_id)"
    >
        @include('backend.rdbprojecttype._form')
    </x-form-wrapper>
</div>
@endsection