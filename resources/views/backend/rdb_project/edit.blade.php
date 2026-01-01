@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <x-form-wrapper
                title="แก้ไขข้อมูลโครงการวิจัย"
                icon="bi-pencil-square"
                mode="edit"
                :backRoute="route('backend.rdb_project.show', $project->pro_id)"
                :actionRoute="route('backend.rdb_project.update', $project->pro_id)"
                method="PUT"
                enctype="multipart/form-data"
            >
                @include('backend.rdb_project._form')
            </x-form-wrapper>
        </div>
    </div>
</div>
@endsection
