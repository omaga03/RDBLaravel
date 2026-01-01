@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-form-wrapper 
        title="เพิ่มประเภทผลงานตีพิมพ์" 
        icon="bi-journal-plus"
        :backRoute="route('backend.rdbpublishedtype.index')"
        :actionRoute="route('backend.rdbpublishedtype.store')"
    >
        @include('backend.rdbpublishedtype._form')
    </x-form-wrapper>
</div>
@endsection
