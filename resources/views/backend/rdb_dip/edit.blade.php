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

            <x-form-wrapper 
                title="แก้ไขข้อมูลทรัพย์สินทางปัญญา" 
                icon="bi-shield-check"
                mode="edit" 
                :backRoute="route('backend.rdb_dip.show', $item->dip_id)"
                :actionRoute="route('backend.rdb_dip.update', $item->dip_id)"
                method="PUT"
                enctype="multipart/form-data"
            >
                @include('backend.rdb_dip._form', ['item' => $item])
            </x-form-wrapper>
        </div>
    </div>
</div>
@endsection
