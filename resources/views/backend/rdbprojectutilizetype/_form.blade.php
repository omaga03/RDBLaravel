<div class="row g-3">
    <div class="col-md-9">
        <label class="form-label required">ชื่อประเภทการนำไปใช้ประโยชน์</label>
        <input type="text" class="form-control" name="utz_typr_name" value="{{ old('utz_typr_name', $item->utz_typr_name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">ลำดับการแสดงผล</label>
        <input type="number" class="form-control" name="utz_type_index" value="{{ old('utz_type_index', $item->utz_type_index ?? '') }}">
    </div>
</div>
