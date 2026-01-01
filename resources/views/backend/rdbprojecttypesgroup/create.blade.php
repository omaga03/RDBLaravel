@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-form-wrapper 
        title="เพิ่มกลุ่มประเภทงบประมาณ" 
        icon="bi-collection"
        mode="create" 
        :backRoute="route('backend.rdbprojecttypesgroup.index')"
        :actionRoute="route('backend.rdbprojecttypesgroup.store')"
    >
        @include('backend.rdbprojecttypesgroup._form')
    </x-form-wrapper>
</div>
@endsection
