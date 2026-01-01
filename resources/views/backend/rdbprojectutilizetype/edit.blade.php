@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-form-wrapper 
        title="แก้ไขประเภทการนำไปใช้ประโยชน์" 
        icon="bi-rocket-takeoff"
        :backRoute="route('backend.rdbprojectutilizetype.index')"
        :actionRoute="route('backend.rdbprojectutilizetype.update', $item->utz_type_id)"
        method="PUT"
        mode="edit"
    >
        @include('backend.rdbprojectutilizetype._form')
    </x-form-wrapper>
</div>
@endsection
