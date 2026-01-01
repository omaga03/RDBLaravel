@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-form-wrapper 
        title="เพิ่มประเภทการนำไปใช้ประโยชน์" 
        icon="bi-rocket-takeoff"
        :backRoute="route('backend.rdbprojectutilizetype.index')"
        :actionRoute="route('backend.rdbprojectutilizetype.store')"
    >
        @include('backend.rdbprojectutilizetype._form')
    </x-form-wrapper>
</div>
@endsection
