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
        title="เพิ่มข้อมูลทรัพย์สินทางปัญญา" 
        icon="bi-shield-check"
        mode="create" 
        :backRoute="route('backend.rdb_dip.index')"
        :actionRoute="route('backend.rdb_dip.store')"
        enctype="multipart/form-data"
    >
        @include('backend.rdb_dip._form')
    </x-form-wrapper>
</div>
@endsection
