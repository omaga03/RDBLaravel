@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="แก้ไขประเภทโครงการทุนย่อย: {{ $item->pts_name }}" 
        icon="bi-pencil-square"
        mode="edit" 
        :actionRoute="route('backend.rdbprojecttypesub.update', $item->pts_id)"
        method="PUT"
        :backRoute="route('backend.rdbprojecttypesub.show', $item->pts_id)"
        enctype="multipart/form-data"
    >
        @include('backend.rdbprojecttypesub._form')
    </x-form-wrapper>
</div>
@endsection
