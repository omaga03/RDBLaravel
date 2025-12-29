@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-danger mb-4 shadow-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('backend.rdb_dip.update', $item->dip_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.rdb_dip._form', ['item' => $item])
            </form>
        </div>
    </div>
</div>
@endsection
