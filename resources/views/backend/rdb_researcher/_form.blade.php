
{{-- Row 1: Citizen ID (Create Only), Scopus, Orchid --}}
<div class="row mb-3">
    @if(!isset($researcher) || !$researcher->exists)
    <div class="col-lg-4">
        <label for="citizen_id" class="form-label">เลขบัตรประจำตัวประชาชน <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="citizen_id" name="citizen_id" 
               value="{{ old('citizen_id') }}" placeholder="0-0000-00000-00-0" required>
        <div class="form-text">ใช้สำหรับสร้าง Researcher Code ID (ไม่สามารถแก้ไขได้ภายหลังในหน้านี้)</div>
    </div>
    @endif
    
    <div class="{{ (!isset($researcher) || !$researcher->exists) ? 'col-lg-4' : 'col-lg-6' }}">
        <label for="scopus_authorId" class="form-label">Scopus Author ID</label>
        <input type="text" class="form-control" id="scopus_authorId" name="scopus_authorId" 
               value="{{ old('scopus_authorId', $researcher->scopus_authorId ?? '') }}" placeholder="9999999999999">
    </div>
    <div class="{{ (!isset($researcher) || !$researcher->exists) ? 'col-lg-4' : 'col-lg-6' }}">
        <label for="orcid" class="form-label">ORCID</label>
        <input type="text" class="form-control" id="orcid" name="orcid" 
               value="{{ old('orcid', $researcher->orcid ?? '') }}" placeholder="0000-0000-0000-0000">
    </div>
</div>

{{-- Row 2: TeaCode, Status --}}
<div class="row mb-3">
    <div class="col-lg-6">
        <label for="tea_code" class="form-label">Tea Code</label>
        <input type="text" class="form-control" id="tea_code" name="tea_code" 
               value="{{ old('tea_code', $researcher->tea_code ?? '') }}">
    </div>
    <div class="col-lg-6">
        <label for="restatus_id" class="form-label">สถานะ</label>
        <select class="form-select" id="restatus_id" name="restatus_id">
            <option value="">เลือกสถานะ...</option>
            @foreach($statuses as $status)
                <option value="{{ $status->restatus_id }}" {{ (old('restatus_id', $researcher->restatus_id ?? '') == $status->restatus_id) ? 'selected' : '' }}>
                    {{ $status->restatus_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<hr>
<h5 class="text-primary mb-3"><i class="bi bi-person-lines-fill"></i> ข้อมูลส่วนตัว</h5>

{{-- Row 3: Prefix, Gender, Fname(TH), Lname(TH) --}}
<div class="row mb-3">
    <div class="col-lg-3">
        <label for="prefix_id" class="form-label">คำนำหน้า</label>
        <select class="form-select" id="prefix_id" name="prefix_id">
            <option value="">คำนำหน้า...</option>
            @foreach($prefixes as $prefix)
                <option value="{{ $prefix->prefix_id }}" {{ (old('prefix_id', $researcher->prefix_id ?? '') == $prefix->prefix_id) ? 'selected' : '' }}>
                    {{ $prefix->prefix_nameTH }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label class="form-label d-block">เพศ</label>
        <div class="d-flex mt-2">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="researcher_gender" id="gender_m" value="M" {{ (old('researcher_gender', $researcher->researcher_gender ?? '') == 'M') ? 'checked' : '' }}>
                <label class="form-check-label" for="gender_m">ชาย</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="researcher_gender" id="gender_f" value="F" {{ (old('researcher_gender', $researcher->researcher_gender ?? '') == 'F') ? 'checked' : '' }}>
                <label class="form-check-label" for="gender_f">หญิง</label>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <label for="researcher_fname" class="form-label">ชื่อ (ไทย) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="researcher_fname" name="researcher_fname" 
               value="{{ old('researcher_fname', $researcher->researcher_fname ?? '') }}" required>
    </div>
    <div class="col-lg-3">
        <label for="researcher_lname" class="form-label">นามสกุล (ไทย) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="researcher_lname" name="researcher_lname" 
               value="{{ old('researcher_lname', $researcher->researcher_lname ?? '') }}" required>
    </div>
</div>

{{-- Row 4: FnameEN, LnameEN --}}
<div class="row mb-3">
    <div class="col-lg-3 offset-lg-6">
        <label for="researcher_fnameEN" class="form-label">FirstName (EN)</label>
        <input type="text" class="form-control" id="researcher_fnameEN" name="researcher_fnameEN" 
               value="{{ old('researcher_fnameEN', $researcher->researcher_fnameEN ?? '') }}">
    </div>
    <div class="col-lg-3">
        <label for="researcher_lnameEN" class="form-label">LastName (EN)</label>
        <input type="text" class="form-control" id="researcher_lnameEN" name="researcher_lnameEN" 
               value="{{ old('researcher_lnameEN', $researcher->researcher_lnameEN ?? '') }}">
    </div>
</div>

{{-- Row 5: Department, DepCategory --}}
<div class="row mb-3">
    <div class="col-lg-6">
        <label for="department_id" class="form-label">คณะ/หน่วยงาน</label>
        <x-tom-select 
            name="department_id"
            id="department_id"
            :options="$departments->pluck('department_nameTH', 'department_id')"
            :value="old('department_id', $researcher->department_id ?? '')"
            placeholder="เลือกคณะ/หน่วยงาน..."
        />
    </div>
    <div class="col-lg-6">
        <label for="depcat_id" class="form-label">สาขาทางวิชาการ</label>
        <x-tom-select 
            name="depcat_id"
            id="depcat_id"
            :options="$categories->pluck('depcat_name', 'depcat_id')"
            :value="old('depcat_id', $researcher->depcat_id ?? '')"
            placeholder="เลือกสาขาทางวิชาการ..."
        />
    </div>
</div>

{{-- Row 6: Addresses --}}
<div class="row mb-3">
    <div class="col-lg-6">
        <label for="researcher_address" class="form-label">ที่อยู่ตามทะเบียนบ้าน</label>
        <textarea class="form-control" id="researcher_address" name="researcher_address" rows="3">{{ old('researcher_address', $researcher->researcher_address ?? '') }}</textarea>
    </div>
    <div class="col-lg-6">
        <label for="researcher_workaddress" class="form-label">ที่อยู่ที่ทำงาน</label>
        <textarea class="form-control" id="researcher_workaddress" name="researcher_workaddress" rows="3">{{ old('researcher_workaddress', $researcher->researcher_workaddress ?? '') }}</textarea>
    </div>
</div>

{{-- Row 7: Contact Info --}}
<div class="row mb-3">
    <div class="col-lg-4">
        <label for="researcher_email" class="form-label">Email</label>
        <input type="email" class="form-control" id="researcher_email" name="researcher_email" 
               value="{{ old('researcher_email', $researcher->researcher_email ?? '') }}">
    </div>
    <div class="col-lg-4">
        <label for="researcher_mobile" class="form-label">เบอร์มือถือ</label>
        <input type="text" class="form-control" id="researcher_mobile" name="researcher_mobile" 
               value="{{ old('researcher_mobile', $researcher->researcher_mobile ?? '') }}" placeholder="00-0000-0000">
    </div>
    <div class="col-lg-4">
        <label for="researcher_tel" class="form-label">เบอร์โทรศัพท์</label>
        <input type="text" class="form-control" id="researcher_tel" name="researcher_tel" 
               value="{{ old('researcher_tel', $researcher->researcher_tel ?? '') }}" placeholder="0-0000-0000">
    </div>
</div>

{{-- Row 8: Image (Create Only) --}}
@if(!isset($researcher) || !$researcher->exists)
<div class="row mb-3">
    <div class="col-md-6">
        <label for="fileimg" class="form-label">รูปถ่ายบโปรไฟล์</label>
        <input type="file" class="form-control" id="fileimg" name="fileimg" accept="image/*">
    </div>
</div>
@endif

{{-- Row 9: Note --}}
<div class="mb-3">
    <label for="researcher_note" class="form-label">หมายเหตุ</label>
    <input type="text" class="form-control" id="researcher_note" name="researcher_note" 
            value="{{ old('researcher_note', $researcher->researcher_note ?? '') }}">
</div>




@push('scripts')
<script src="https://unpkg.com/imask"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto Gender Selection based on Prefix
        const prefixSelect = document.getElementById('prefix_id');
        const genderMale = document.getElementById('gender_m');
        const genderFemale = document.getElementById('gender_f');

        if(prefixSelect) {
            prefixSelect.addEventListener('change', function() {
                const val = this.value;
                if(val == 1) { // Mr. -> Male
                    genderMale.checked = true;
                } else if (val == 2 || val == 3) { // Miss/Mrs -> Female
                    genderFemale.checked = true;
                }
            });
        }

        // Input Masking
        const masks = [
            { id: 'scopus_authorId', mask: '0000000000000' },
            { id: 'orcid', mask: '0000-0000-0000-0000' },
            { id: 'citizen_id', mask: '0-0000-00000-00-0' }, 
            { id: 'researcher_mobile', mask: '00-0000-0000' },
            { id: 'researcher_tel', mask: '0-0000-0000' }
        ];

        masks.forEach(config => {
            const el = document.getElementById(config.id);
            if (el) {
                IMask(el, {
                    mask: config.mask
                });
            }
        });
    });
</script>
@endpush
