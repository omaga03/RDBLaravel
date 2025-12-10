@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มการทำงานในผลงานตีพิมพ์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedwork.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="published_id" class="form-label">ผลงานตีพิมพ์ <span class="text-danger">*</span></label>
                    <select class="form-select @error('published_id') is-invalid @enderror" id="published_id" name="published_id" required>
                        <option value="">-- เลือกผลงานตีพิมพ์ --</option>
                        @foreach($publisheds as $published)
                            <option value="{{ $published->published_id }}" {{ old('published_id') == $published->published_id ? 'selected' : '' }}>{{ $published->published_nameTH }}</option>
                        @endforeach
                    </select>
                    @error('published_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="researcher_id" class="form-label">นักวิจัย</label>
                    <select class="form-select" id="researcher_id" name="researcher_id">
                        <option value="">-- เลือกนักวิจัย --</option>
                        @foreach($researchers as $researcher)
                            <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id') == $researcher->researcher_id ? 'selected' : '' }}>{{ $researcher->researcher_nameTH }} {{ $researcher->researcher_surnameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="pubta_id" class="form-label">ประเภทผู้แต่ง</label>
                    <select class="form-select" id="pubta_id" name="pubta_id">
                        <option value="">-- เลือกประเภทผู้แต่ง --</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->pubta_id }}" {{ old('pubta_id') == $author->pubta_id ? 'selected' : '' }}>{{ $author->pubta_nameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="pubw_main" class="form-label">เป็นผู้รับผิดชอบหลัก (Y/N)</label>
                    <input type="text" class="form-control" id="pubw_main" name="pubw_main" value="{{ old('pubw_main') }}" maxlength="1">
                </div>
                <div class="mb-3">
                    <label for="pubw_bud" class="form-label">สัดส่วน (%)</label>
                    <input type="number" step="0.01" class="form-control" id="pubw_bud" name="pubw_bud" value="{{ old('pubw_bud') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedwork.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
