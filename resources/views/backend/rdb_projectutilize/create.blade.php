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
        title="เพิ่มข้อมูลการใช้ประโยชน์" 
        icon="bi-rocket-takeoff"
        mode="create" 
        :backRoute="route('backend.rdbprojectutilize.index')"
        :actionRoute="route('backend.rdbprojectutilize.store')"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_projectutilize._form')
    </x-form-wrapper>
</div>

@endsection
