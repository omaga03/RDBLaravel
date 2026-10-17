@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
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
        title="เพิ่มข่าว/กิจกรรมใหม่" 
        icon="bi-newspaper"
        mode="create" 
        :actionRoute="route('backend.research_news.store')" 
        :backRoute="route('backend.research_news.index')" 
        enctype="multipart/form-data"
    >
        @include('backend.research_news._form')
    </x-form-wrapper>
</div>
@endsection
