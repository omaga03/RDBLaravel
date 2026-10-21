<div class="row g-3">
    <div class="col-md-4">
        <label for="changwat_t" class="form-label fw-bold">จังหวัด (ภาษาไทย) <span class="text-danger">*</span></label>
        <input type="text" name="changwat_t" id="changwat_t" class="form-control @error('changwat_t') is-invalid @enderror" value="{{ old('changwat_t', $item->changwat_t ?? '') }}" placeholder="เช่น จ.เพชรบูรณ์" required>
        @error('changwat_t')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="amphoe_t" class="form-label fw-bold">อำเภอ (ภาษาไทย) <span class="text-danger">*</span></label>
        <input type="text" name="amphoe_t" id="amphoe_t" class="form-control @error('amphoe_t') is-invalid @enderror" value="{{ old('amphoe_t', $item->amphoe_t ?? '') }}" placeholder="เช่น อ.เมืองเพชรบูรณ์" required>
        @error('amphoe_t')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="tambon_t" class="form-label fw-bold">ตำบล (ภาษาไทย) <span class="text-danger">*</span></label>
        <input type="text" name="tambon_t" id="tambon_t" class="form-control @error('tambon_t') is-invalid @enderror" value="{{ old('tambon_t', $item->tambon_t ?? '') }}" placeholder="เช่น ต.สะเดียง" required>
        @error('tambon_t')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="changwat_e" class="form-label fw-bold">จังหวัด (English)</label>
        <input type="text" name="changwat_e" id="changwat_e" class="form-control @error('changwat_e') is-invalid @enderror" value="{{ old('changwat_e', $item->changwat_e ?? '') }}" placeholder="e.g. Phetchabun">
        @error('changwat_e')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="amphoe_e" class="form-label fw-bold">อำเภอ (English)</label>
        <input type="text" name="amphoe_e" id="amphoe_e" class="form-control @error('amphoe_e') is-invalid @enderror" value="{{ old('amphoe_e', $item->amphoe_e ?? '') }}" placeholder="e.g. Mueang Phetchabun">
        @error('amphoe_e')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="tambon_e" class="form-label fw-bold">ตำบล (English)</label>
        <input type="text" name="tambon_e" id="tambon_e" class="form-control @error('tambon_e') is-invalid @enderror" value="{{ old('tambon_e', $item->tambon_e ?? '') }}" placeholder="e.g. Sadiang">
        @error('tambon_e')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="lat" class="form-label fw-bold"><i class="bi bi-geo-alt text-danger me-1"></i>ละติจูด (Latitude)</label>
        <input type="number" step="0.000001" name="lat" id="lat" class="form-control @error('lat') is-invalid @enderror" value="{{ old('lat', $item->lat ?? '') }}" placeholder="เช่น 16.425310">
        <div class="form-text small">พิกัดทางภูมิศาสตร์ ละติจูด (North/South)</div>
        @error('lat')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="long" class="form-label fw-bold"><i class="bi bi-geo-alt text-danger me-1"></i>ลองจิจูด (Longitude)</label>
        <input type="number" step="0.000001" name="long" id="long" class="form-control @error('long') is-invalid @enderror" value="{{ old('long', $item->long ?? '') }}" placeholder="เช่น 101.157840">
        <div class="form-text small">พิกัดทางภูมิศาสตร์ ลองจิจูด (East/West)</div>
        @error('long')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
