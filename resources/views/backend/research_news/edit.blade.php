@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">แก้ไขข่าว/กิจกรรม</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.research_news.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.research_news._form')
            </form>
        </div>
    </div>
</div>
@endsection
