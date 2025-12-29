@php
    $isEdit = isset($item) && $item->dip_id;
@endphp

<div class="card shadow-sm border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-shield-check"></i> {{ $isEdit ? 'แก้ไขข้อมูลทรัพย์สินทางปัญญา' : 'เพิ่มข้อมูลทรัพย์สินทางปัญญา' }}</h5>
        @if($isEdit)
            <small>{{ $item->dip_data2_name }}</small>
        @endif
    </div>
    <div class="card-body">
        {{-- Section 1: ข้อมูลหลัก --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="dip_request_number" class="form-label fw-bold">เลขที่คำขอ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="dip_request_number" name="dip_request_number" 
                       value="{{ old('dip_request_number', $item->dip_request_number ?? '') }}" required placeholder="ระบุเลขที่คำขอ...">
            </div>
            <div class="col-md-4 mb-3">
                <label for="dipt_id" class="form-label fw-bold">ประเภททรัพย์สินทางปัญญา <span class="text-danger">*</span></label>
                <select class="form-select" id="dipt_id" name="dipt_id" required>
                    <option value="">-- เลือกประเภท --</option>
                    @foreach(\App\Models\RdbDipType::all() as $type)
                        <option value="{{ $type->dipt_id }}" {{ (old('dipt_id', $item->dipt_id ?? '') == $type->dipt_id) ? 'selected' : '' }}>
                            {{ $type->dipt_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="dip_request_date" class="form-label fw-bold">วันที่ยื่นคำขอ</label>
                <input type="text" class="form-control datepicker" id="dip_request_date" name="dip_request_date" 
                       value="{{ old('dip_request_date', $item->dip_request_date ?? '') }}" placeholder="วว/ดด/ปปปป">
            </div>
        </div>

        <div class="mb-3">
            <label for="dip_data2_name" class="form-label fw-bold">ชื่อผลงาน / ชื่อการประดิษฐ์ <span class="text-danger">*</span></label>
            <textarea class="form-control" id="dip_data2_name" name="dip_data2_name" rows="2" required placeholder="ระบุชื่อผลงาน...">{{ old('dip_data2_name', $item->dip_data2_name ?? '') }}</textarea>
        </div>

        {{-- Section 2: รายละเอียดทางทะเบียน --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="dip_patent_number" class="form-label fw-bold">เลขที่สิทธิบัตร (Patent No.)</label>
                <input type="text" class="form-control" id="dip_patent_number" name="dip_patent_number" 
                       value="{{ old('dip_patent_number', $item->dip_patent_number ?? '') }}" placeholder="ระบุเลขที่สิทธิบัตร...">
            </div>
            <div class="col-md-4 mb-3">
                <label for="dip_number" class="form-label fw-bold">เลขที่ทะเบียน (เมื่อได้รับ)</label>
                <input type="text" class="form-control" id="dip_number" name="dip_number" 
                       value="{{ old('dip_number', $item->dip_number ?? '') }}" placeholder="ระบุเลขที่ทะเบียน...">
            </div>
            <div class="col-md-4 mb-3">
                <label for="dip_data2_agent" class="form-label fw-bold">เลขที่สิทธิบัตร (Legacy) / ตัวแทน</label>
                <input type="text" class="form-control" id="dip_data2_agent" name="dip_data2_agent" 
                       value="{{ old('dip_data2_agent', $item->dip_data2_agent ?? '') }}" placeholder="ระบุข้อมูลเดิม...">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="dip_publication_no" class="form-label fw-bold">เลขที่ประกาศโฆษณา</label>
                <input type="text" class="form-control" id="dip_publication_no" name="dip_publication_no" 
                       value="{{ old('dip_publication_no', $item->dip_publication_no ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="dip_publication_date" class="form-label fw-bold">วันที่ประกาศโฆษณา</label>
                <input type="text" class="form-control datepicker" id="dip_publication_date" name="dip_publication_date" 
                       value="{{ old('dip_publication_date', $item->dip_publication_date ?? '') }}" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-4 mb-3">
                <label for="dip_data2_status" class="form-label fw-bold">สถานะคำขอปัจจุบัน</label>
                <input type="text" class="form-control" id="dip_data2_status" name="dip_data2_status" 
                       value="{{ old('dip_data2_status', $item->dip_data2_status ?? '') }}" placeholder="ระบุสถานะ...">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="dip_data2_dateend" class="form-label fw-bold">วันสิ้นสุดอายุ / วันหมดอายุ</label>
                <input type="text" class="form-control datepicker" id="dip_data2_dateend" name="dip_data2_dateend" 
                       value="{{ old('dip_data2_dateend', $item->dip_data2_dateend ?? '') }}" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-8 mb-3">
                <label for="dip_url" class="form-label fw-bold">ลิงก์ข้อมูลเพิ่มเติม (URL)</label>
                <input type="url" class="form-control" id="dip_url" name="dip_url" 
                       value="{{ old('dip_url', $item->dip_url ?? '') }}" placeholder="https://...">
            </div>
        </div>

        {{-- Section 3: นักวิจัยและโครงการ --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="researcher_id" class="form-label fw-bold">นักวิจัย/ผู้ประดิษฐ์หลัก <span class="text-danger">*</span></label>
                <select class="form-select" id="researcher_id" name="researcher_id" required>
                    @if(isset($item) && $item->researcher)
                        <option value="{{ $item->researcher_id }}" selected>{{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-8 mb-3">
                <label for="pro_id" class="form-label fw-bold">โครงการวิจัยที่เกี่ยวข้อง</label>
                <select class="form-select" id="pro_id" name="pro_id">
                    @if(isset($item) && $item->project)
                        <option value="{{ $item->pro_id }}" selected>{{ $item->project->pro_nameTH }}</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="dip_note" class="form-label fw-bold">หมายเหตุ</label>
            <textarea class="form-control" id="dip_note" name="dip_note" rows="2" placeholder="ระบุหมายเหตุเพิ่มเติม...">{{ old('dip_note', $item->dip_note ?? '') }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="form-group mt-4 border-top pt-3">
            <button type="submit" class="btn btn-{{ $isEdit ? 'primary' : 'success' }} px-4">
                <i class="bi bi-save"></i> บันทึกข้อมูล
            </button>
            <a href="{{ $isEdit ? route('backend.rdb_dip.show', $item->dip_id) : route('backend.rdb_dip.index') }}" class="btn btn-warning">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
    </div>
</div>

@push('scripts')
<!-- TomSelect CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    /* TomSelect Dark Mode Support */
    [data-bs-theme="dark"] .ts-wrapper .ts-control { background-color: #212529; border-color: #495057; color: #fff; }
    [data-bs-theme="dark"] .ts-wrapper .ts-control input { color: #fff !important; }
    [data-bs-theme="dark"] .ts-dropdown { background-color: #2b3035; border-color: #495057; color: #fff; }
    [data-bs-theme="dark"] .ts-dropdown .option { color: #fff; }
    [data-bs-theme="dark"] .ts-dropdown .option:hover, [data-bs-theme="dark"] .ts-dropdown .option.active { background-color: #0d6efd; }
    [data-bs-theme="dark"] .ts-wrapper .ts-control .item { background-color: #0d6efd; color: #fff; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Researcher Search (TomSelect)
    const researcherSelect = document.getElementById('researcher_id');
    if (researcherSelect && typeof TomSelect !== 'undefined') {
        new TomSelect(researcherSelect, {
            create: false,
            placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย --',
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("backend.rdb_project.search_researchers") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            }
        });
    }

    // Project Search (TomSelect)
    const projectSelect = document.getElementById('pro_id');
    if (projectSelect && typeof TomSelect !== 'undefined') {
        new TomSelect(projectSelect, {
            create: false,
            placeholder: '-- พิมพ์เพื่อค้นหาโครงการ --',
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("backend.rdb_project.search") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            }
        });
    }

    // Thai Buddhist Datepicker using global helper
    if (typeof initThaiFlatpickr !== 'undefined') {
        initThaiFlatpickr(".datepicker");
    }
});
</script>
@endpush
