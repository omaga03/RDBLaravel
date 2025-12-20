@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">เพิ่มข่าว/กิจกรรมใหม่</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.research_news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('backend.research_news._form')
            </form>
        </div>
    </div>
</div>
@endsection
