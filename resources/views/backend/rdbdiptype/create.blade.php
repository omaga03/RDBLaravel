@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-form-wrapper 
        title="เพิ่มประเภททรัพย์สินทางปัญญา" 
        icon="bi-lightbulb"
        :backRoute="route('backend.rdbdiptype.index')"
        :actionRoute="route('backend.rdbdiptype.store')"
    >
        @include('backend.rdbdiptype._form')
    </x-form-wrapper>
</div>
@endsection
