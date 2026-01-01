@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @if ($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <x-form-wrapper 
        title="แก้ไขข้อมูลการใช้ประโยชน์" 
        icon="bi-pencil-square"
        mode="edit" 
        :backRoute="route('backend.rdbprojectutilize.show', $item->utz_id)"
        :actionRoute="route('backend.rdbprojectutilize.update', $item->utz_id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_projectutilize._form', ['item' => $item])
    </x-form-wrapper>
</div>

@endsection
