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
        title="แก้ไขข่าวงานประชุมวิชาการ" 
        icon="bi-pencil-square"
        mode="edit" 
        action="{{ route('backend.research_conference.update', $item->id) }}" 
        method="PUT"
        enctype="multipart/form-data"
    >
        @include('backend.research_conference._form', ['item' => $item])
    </x-form-wrapper>
</div>
@endsection
