@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if ($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <x-form-wrapper 
        title="เพิ่มข่าวงานประชุมวิชาการใหม่" 
        icon="bi-calendar-event"
        mode="create" 
        action="{{ route('backend.research_conference.store') }}" 
        enctype="multipart/form-data"
    >
        @include('backend.research_conference._form')
    </x-form-wrapper>
</div>
@endsection
