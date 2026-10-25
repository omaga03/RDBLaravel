<div class="row g-3">
    <div class="col-md-5">
        <label for="pubta_nameTH" class="form-label fw-bold">ชื่อประเภทผู้แต่ง (ภาษาไทย) <span class="text-danger">*</span></label>
        <input type="text" name="pubta_nameTH" id="pubta_nameTH" class="form-control @error('pubta_nameTH') is-invalid @enderror" value="{{ old('pubta_nameTH', $item->pubta_nameTH ?? '') }}" required placeholder="เช่น ผู้ประพันธ์อันดับแรก">
        @error('pubta_nameTH')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-5">
        <label for="pubta_nameEN" class="form-label fw-bold">ชื่อประเภทผู้แต่ง (ภาษาอังกฤษ)</label>
        <input type="text" name="pubta_nameEN" id="pubta_nameEN" class="form-control @error('pubta_nameEN') is-invalid @enderror" value="{{ old('pubta_nameEN', $item->pubta_nameEN ?? '') }}" placeholder="เช่น First Author">
        @error('pubta_nameEN')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label for="pubta_score" class="form-label fw-bold">ค่าคะแนน</label>
        <input type="number" step="0.01" name="pubta_score" id="pubta_score" class="form-control @error('pubta_score') is-invalid @enderror" value="{{ old('pubta_score', $item->pubta_score ?? '') }}" placeholder="เช่น 1.00">
        @error('pubta_score')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
