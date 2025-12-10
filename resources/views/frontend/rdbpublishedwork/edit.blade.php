@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขการทำงานในผลงานตีพิมพ์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedwork.update', $item->published_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">ผลงานตีพิมพ์</label>
                    <input type="text" class="form-control" value="{{ $item->published->published_nameTH ?? $item->published_id }}" disabled>
                    <div class="form-text">ไม่สามารถแก้ไขผลงานตีพิมพ์ได้</div>
                </div>
                <div class="mb-3">
                    <label for="researcher_id" class="form-label">นักวิจัย</label>
                    <select class="form-select" id="researcher_id" name="researcher_id">
                        <option value="">-- เลือกนักวิจัย --</option>
                        @foreach($researchers as $researcher)
                            <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id', $item->researcher_id) == $researcher->researcher_id ? 'selected' : '' }}>{{ $researcher->researcher_nameTH }} {{ $researcher->researcher_surnameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="pubta_id" class="form-label">ประเภทผู้แต่ง</label>
                    <select class="form-select" id="pubta_id" name="pubta_id">
                        <option value="">-- เลือกประเภทผู้แต่ง --</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->pubta_id }}" {{ old('pubta_id', $item->pubta_id) == $author->pubta_id ? 'selected' : '' }}>{{ $author->pubta_nameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="pubw_main" class="form-label">เป็นผู้รับผิดชอบหลัก (Y/N)</label>
                    <input type="text" class="form-control" id="pubw_main" name="pubw_main" value="{{ old('pubw_main', $item->pubw_main) }}" maxlength="1">
                </div>
                <div class="mb-3">
                    <label for="pubw_bud" class="form-label">สัดส่วน (%)</label>
                    <input type="number" step="0.01" class="form-control" id="pubw_bud" name="pubw_bud" value="{{ old('pubw_bud', $item->pubw_bud) }}">
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
