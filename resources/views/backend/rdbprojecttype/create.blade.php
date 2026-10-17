@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มประเภททุนใหม่" 
        icon="bi-tag"
        mode="create" 
        :actionRoute="route('backend.rdbprojecttype.store')"
        :backRoute="route('backend.rdbprojecttype.index')"
    >
        @include('backend.rdbprojecttype._form')
    </x-form-wrapper>
</div>
@endsection