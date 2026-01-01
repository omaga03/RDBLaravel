<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label required">กลุ่มประเภท (Group)</label>
        <input type="text" class="form-control" name="pubtype_group" value="{{ old('pubtype_group', $item->pubtype_group ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">ประเภทย่อย (Group Type)</label>
        <input type="text" class="form-control" name="pubtype_grouptype" value="{{ old('pubtype_grouptype', $item->pubtype_grouptype ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">กลุ่มย่อย (Subgroup)</label>
        <input type="text" class="form-control" name="pubtype_subgroup" value="{{ old('pubtype_subgroup', $item->pubtype_subgroup ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">คะแนนมาตรฐาน</label>
        <input type="number" step="0.01" class="form-control" name="pubtype_score" value="{{ old('pubtype_score', $item->pubtype_score ?? '') }}">
    </div>
</div>
