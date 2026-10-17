@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มไฟล์แนบใหม่" 
        icon="bi-file-earmark-plus"
        mode="create" 
        :actionRoute="route('backend.rdbprojectfiles.store')"
        :backRoute="route('backend.rdbprojectfiles.index')"
    >
        @include('backend.rdbprojectfiles._form')
    </x-form-wrapper>
</div>
@endsection