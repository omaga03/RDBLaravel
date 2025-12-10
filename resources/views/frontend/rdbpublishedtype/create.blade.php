@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มประเภทผลงานตีพิมพ์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedtype.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="pubtype_group" class="form-label">กลุ่มประเภท</label>
                    <input type="text" class="form-control" id="pubtype_group" name="pubtype_group" value="{{ old('pubtype_group') }}">
                </div>
                <div class="mb-3">
                    <label for="pubtype_grouptype" class="form-label">ชนิดกลุ่ม</label>
                    <input type="text" class="form-control" id="pubtype_grouptype" name="pubtype_grouptype" value="{{ old('pubtype_grouptype') }}">
                </div>
                <div class="mb-3">
                    <label for="pubtype_subgroup" class="form-label">กลุ่มย่อย</label>
                    <input type="text" class="form-control" id="pubtype_subgroup" name="pubtype_subgroup" value="{{ old('pubtype_subgroup') }}">
                </div>
                <div class="mb-3">
                    <label for="pubtype_score" class="form-label">คะแนน</label>
                    <input type="number" step="0.01" class="form-control" id="pubtype_score" name="pubtype_score" value="{{ old('pubtype_score') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedtype.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
