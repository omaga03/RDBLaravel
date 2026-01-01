@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <x-form-wrapper 
        title="เพิ่มโครงการวิจัย" 
        icon="bi-pencil-square"
        mode="create" 
        :backRoute="route('backend.rdb_project.index')"
        :actionRoute="route('backend.rdb_project.store')"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_project._form')
    </x-form-wrapper>
</div>

@endsection
