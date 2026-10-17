@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มประเภทโครงการทุนย่อย" 
        icon="bi-tags"
        mode="create" 
        :actionRoute="route('backend.rdbprojecttypesub.store')"
        :backRoute="route('backend.rdbprojecttypesub.index')"
        enctype="multipart/form-data"
    >
        @include('backend.rdbprojecttypesub._form')
    </x-form-wrapper>
</div>
@endsection
