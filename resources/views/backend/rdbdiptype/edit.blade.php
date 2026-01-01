@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-form-wrapper 
        title="แก้ไขประเภททรัพย์สินทางปัญญา" 
        icon="bi-lightbulb"
        :backRoute="route('backend.rdbdiptype.index')"
        :actionRoute="route('backend.rdbdiptype.update', $item->dipt_id)"
        method="PUT"
        mode="edit"
    >
        @include('backend.rdbdiptype._form')
    </x-form-wrapper>
</div>
@endsection
