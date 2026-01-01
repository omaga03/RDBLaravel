@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-form-wrapper 
        title="แก้ไขประเภทผลงานตีพิมพ์" 
        icon="bi-journal-text"
        :backRoute="route('backend.rdbpublishedtype.index')"
        :actionRoute="route('backend.rdbpublishedtype.update', $item->pubtype_id)"
        method="PUT"
        mode="edit"
    >
        @include('backend.rdbpublishedtype._form')
    </x-form-wrapper>
</div>
@endsection
