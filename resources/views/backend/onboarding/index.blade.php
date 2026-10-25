@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            
            <!-- Header -->
            <div class="text-center mb-4">
                <div class="inline-flex p-3 rounded-circle bg-primary-subtle text-primary mb-2 shadow-sm d-inline-block">
                    <i class="bi bi-person-bounding-box fs-2"></i>
                </div>
                <h3 class="fw-bold text-body mb-1">ขั้นตอนการยืนยันตัวตนและสังกัดนักวิจัย</h3>
                <p class="text-body-secondary mb-0">ระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB)</p>
            </div>

            <!-- Session Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-warning"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-1"></i> กรุณาตรวจสอบข้อมูลที่กรอก:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Status Card -->
            @if($user->onboarding_status === 'pending')
                <div class="card border-warning shadow-sm mb-4">
                    <div class="card-header bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
                            <span class="fw-bold fs-6"><i class="bi bi-hourglass-split me-1"></i> สถานะ: ส่งคำขอแล้ว อยู่ระหว่างรอผู้ดูแลระบบตรวจสอบและปลดล็อก</span>
                        </div>
                        <span class="badge bg-warning text-dark px-2.5 py-1">รอการอนุมัติ (Pending)</span>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-body mb-3">
                            คำขอยืนยันตัวตนของท่านถูกบันทึกในระบบเรียบร้อยแล้ว เมื่อผู้ดูแลระบบ (Admin) ทำการตรวจสอบและปลดล็อกสิทธิ์ ระบบจะเปิดให้ท่านเข้าใช้งานการบริหารจัดการข้อมูลงานวิจัยหลังบ้านทั้งหมดทันที
                        </p>
                        
                        <div class="bg-body-tertiary rounded-3 p-3 mb-3 border">
                            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-clipboard-check me-1"></i> ข้อมูลที่ท่านยื่นขอไว้:</h6>
                            <div class="row g-2 small">
                                <div class="col-sm-4 text-body-secondary">ชื่อ-นามสกุล (ไทย):</div>
                                <div class="col-sm-8 fw-semibold text-body">
                                    {{ $user->onboardingPrefix?->prefix_nameTH ?? '' }}{{ $user->onboarding_fname }} {{ $user->onboarding_lname }}
                                </div>

                                @if($user->onboarding_fname_en || $user->onboarding_lname_en)
                                <div class="col-sm-4 text-body-secondary">Name (English):</div>
                                <div class="col-sm-8 fw-semibold text-body">
                                    {{ $user->onboarding_fname_en }} {{ $user->onboarding_lname_en }}
                                </div>
                                @endif

                                <div class="col-sm-4 text-body-secondary">สังกัด / คณะ:</div>
                                <div class="col-sm-8 fw-semibold text-primary">
                                    <i class="bi bi-building me-1"></i>{{ $user->onboardingDepartment?->department_nameTH ?? 'ไม่ได้ระบุ' }}
                                </div>

                                @if($user->onboarding_position)
                                <div class="col-sm-4 text-body-secondary">ตำแหน่งงาน/วิชาการ:</div>
                                <div class="col-sm-8 text-body">{{ $user->onboarding_position }}</div>
                                @endif

                                @if($user->onboarding_tel)
                                <div class="col-sm-4 text-body-secondary">เบอร์โทรติดต่อ:</div>
                                <div class="col-sm-8 text-body">{{ $user->onboarding_tel }}</div>
                                @endif

                                <div class="col-sm-4 text-body-secondary">วันที่ยื่นคำขอ:</div>
                                <div class="col-sm-8 text-body-secondary">
                                    {{ $user->onboarding_submitted_at ? \Carbon\Carbon::parse($user->onboarding_submitted_at)->format('d/m/Y H:i น.') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <a href="{{ route('site.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center">
                                <i class="bi bi-house me-1"></i> กลับสู่หน้าเว็บไซต์หลัก (หน้าบ้าน)
                            </a>
                            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" onclick="toggleEditForm()">
                                <i class="bi bi-pencil-square me-1"></i> แก้ไขข้อมูลคำขอ
                            </button>
                        </div>
                    </div>
                </div>
            @elseif($user->onboarding_status === 'rejected')
                <div class="card border-danger shadow-sm mb-4">
                    <div class="card-header bg-danger-subtle text-danger-emphasis d-flex align-items-center justify-content-between py-3">
                        <span class="fw-bold fs-6"><i class="bi bi-x-circle-fill me-1"></i> สถานะ: คำขอไม่ผ่านการอนุมัติ</span>
                        <span class="badge bg-danger text-white px-2.5 py-1">ไม่อนุมัติ (Rejected)</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-danger mb-3">
                            <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> เหตุผลจากผู้ดูแลระบบ:</div>
                            <div>{{ $user->onboarding_reject_reason ?? 'ข้อมูลไม่ครบถ้วนหรือไม่สอดคล้องกับฐานข้อมูลบุคลากร' }}</div>
                        </div>
                        <p class="text-body-secondary mb-0">
                            ท่านสามารถปรับปรุงข้อมูลด้านล่างให้ถูกต้อง และกด <strong>"ส่งคำขอใหม่อีกครั้ง"</strong> เพื่อให้ผู้ดูแลระบบตรวจสอบใหม่
                        </p>
                    </div>
                </div>
            @else
                <!-- Unsubmitted Prompt -->
                <div class="card border-primary-subtle shadow-sm mb-4 bg-primary-subtle bg-opacity-25">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-info-circle-fill fs-3 text-primary flex-shrink-0 mt-1"></i>
                            <div>
                                <h5 class="fw-bold text-body mb-1">ยังไม่พบข้อมูลนักวิจัยหรือสังกัดคณะในระบบ</h5>
                                <p class="text-body-secondary mb-0">
                                    เนื่องจากบัญชีอีเมล <code>{{ $user->email }}</code> ยังไม่ได้เชื่อมโยงกับฐานข้อมูลนักวิจัยเดิมของมหาวิทยาลัย กรุณาเลือกคณะ/หน่วยงานต้นสังกัด และกรอกข้อมูลส่วนตัวของท่านด้านล่าง เพื่อส่งให้ผู้ดูแลระบบ (Admin) ตรวจสอบและปลดล็อกการใช้งานหลังบ้าน
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Card -->
            <div class="card shadow-sm border" id="onboardingFormCard" style="{{ $user->onboarding_status === 'pending' ? 'display: none;' : '' }}">
                <div class="card-header bg-body-tertiary py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>แบบฟอร์มยืนยันตัวตนและเลือกสังกัด
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('backend.onboarding.store') }}" method="POST">
                        @csrf

                        <!-- Account Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-body-secondary small fw-semibold">ชื่อผู้ใช้ในระบบ (Username)</label>
                                <input type="text" class="form-control bg-body-secondary" value="{{ $user->username }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-body-secondary small fw-semibold">อีเมลติดต่อ (Email)</label>
                                <input type="email" class="form-control bg-body-secondary" value="{{ $user->email }}" readonly>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Section 1: Department Selection -->
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-building me-1"></i> 1. เลือกสังกัด / คณะ / หน่วยงาน
                        </h6>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">คณะ / สำนัก / สถาบันต้นสังกัด <span class="text-danger">*</span></label>
                            <select name="onboarding_department_id" class="form-select @error('onboarding_department_id') is-invalid @enderror" required>
                                <option value="">-- กรุณาเลือกสังกัด/คณะ --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ old('onboarding_department_id', $user->onboarding_department_id) == $dept->department_id ? 'selected' : '' }}>
                                        {{ $dept->department_nameTH }} {{ $dept->department_nameEN ? '('.$dept->department_nameEN.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('onboarding_department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">เลือกหน่วยงานที่ท่านสังกัดอยู่ในปัจจุบัน เพื่อให้ผู้ประสานงานและผู้ดูแลระบบรับทราบ</div>
                        </div>

                        <hr class="my-3">

                        <!-- Section 2: Personal Info -->
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-person-lines-fill me-1"></i> 2. กรอกข้อมูลส่วนตัวนักวิจัย
                        </h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">คำนำหน้าชื่อ <span class="text-danger">*</span></label>
                                <select name="onboarding_prefix_id" class="form-select @error('onboarding_prefix_id') is-invalid @enderror" required>
                                    <option value="">-- เลือกคำนำหน้า --</option>
                                    @foreach($prefixes as $prefix)
                                        <option value="{{ $prefix->prefix_id }}" {{ old('onboarding_prefix_id', $user->onboarding_prefix_id) == $prefix->prefix_id ? 'selected' : '' }}>
                                            {{ $prefix->prefix_nameTH }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('onboarding_prefix_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ชื่อ (ภาษาไทย) <span class="text-danger">*</span></label>
                                <input type="text" name="onboarding_fname" class="form-control @error('onboarding_fname') is-invalid @enderror" 
                                       value="{{ old('onboarding_fname', $user->onboarding_fname) }}" placeholder="ระบุชื่อจริงภาษาไทย" required>
                                @error('onboarding_fname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">นามสกุล (ภาษาไทย) <span class="text-danger">*</span></label>
                                <input type="text" name="onboarding_lname" class="form-control @error('onboarding_lname') is-invalid @enderror" 
                                       value="{{ old('onboarding_lname', $user->onboarding_lname) }}" placeholder="ระบุนามสกุลภาษาไทย" required>
                                @error('onboarding_lname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name (English)</label>
                                <input type="text" name="onboarding_fname_en" class="form-control @error('onboarding_fname_en') is-invalid @enderror" 
                                       value="{{ old('onboarding_fname_en', $user->onboarding_fname_en) }}" placeholder="e.g. Somsak">
                                @error('onboarding_fname_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name (English)</label>
                                <input type="text" name="onboarding_lname_en" class="form-control @error('onboarding_lname_en') is-invalid @enderror" 
                                       value="{{ old('onboarding_lname_en', $user->onboarding_lname_en) }}" placeholder="e.g. Jaidee">
                                @error('onboarding_lname_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ตำแหน่งทางวิชาการ / ตำแหน่งงาน</label>
                                <input type="text" name="onboarding_position" class="form-control @error('onboarding_position') is-invalid @enderror" 
                                       value="{{ old('onboarding_position', $user->onboarding_position) }}" placeholder="เช่น อาจารย์, นักวิจัย, เจ้าหน้าที่บริหารงานทั่วไป">
                                @error('onboarding_position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">เบอร์โทรศัพท์ติดต่อ / มือถือ</label>
                                <input type="text" name="onboarding_tel" class="form-control @error('onboarding_tel') is-invalid @enderror" 
                                       value="{{ old('onboarding_tel', $user->onboarding_tel) }}" placeholder="เช่น 081-234-5678 หรือ 056-717100">
                                @error('onboarding_tel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('site.index') }}" class="btn btn-light border">
                                <i class="bi bi-arrow-left me-1"></i> ยกเลิก / กลับหน้าบ้าน
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-send-check-fill me-1"></i> 
                                {{ $user->onboarding_status === 'pending' ? 'บันทึกการแก้ไขและส่งคำขอ' : 'บันทึกและส่งคำขอยืนยันตัวตนต่อผู้ดูแลระบบ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleEditForm() {
    const card = document.getElementById('onboardingFormCard');
    if (card) {
        if (card.style.display === 'none' || card.style.display === '') {
            card.style.display = 'block';
            card.scrollIntoView({ behavior: 'smooth' });
        } else {
            card.style.display = 'none';
        }
    }
}
</script>
@endsection
