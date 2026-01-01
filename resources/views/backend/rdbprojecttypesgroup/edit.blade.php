@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="แก้ไขกลุ่มประเภทงบประมาณ: {{ $item->pttg_name }}" 
        icon="bi-pencil-square"
        mode="edit" 
        :backRoute="route('backend.rdbprojecttypesgroup.index')"
        :actionRoute="route('backend.rdbprojecttypesgroup.update', $item->pttg_id)"
        method="PUT"
    >
        @include('backend.rdbprojecttypesgroup._form', ['item' => $item])
    </x-form-wrapper>
</div>
@endsection
