@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="แก้ไขข้อมูลผู้ใช้งาน: {{ $user->username }}" 
        icon="bi-pencil-square"
        mode="edit" 
        :actionRoute="route('backend.user.update', $user->id)" 
        method="PUT"
        :backRoute="$canManageRoles ? route('backend.user.index') : route('backend.site.index')" 
    >
        <div class="row g-3">
            <div class="col-md-6">
                <label for="username" class="form-label fw-bold">ชื่อผู้ใช้ (Username) <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-bold">อีเมล (Email) <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label fw-bold">รหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="กรอกเฉพาะเมื่อต้องการตั้งรหัสผ่านใหม่">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">บทบาท (Role) <span class="text-danger">*</span></label>
                @if($canManageRoles)
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- ค้นหาและเลือกบทบาท --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ old('role', $userRole) == $r->name ? 'selected' : '' }}>
                                {{ $r->name }} {{ $r->description ? '('.$r->description.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @else
                    <div class="form-control bg-body-tertiary d-flex align-items-center justify-content-between">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                            <i class="bi bi-shield-lock me-1"></i>{{ $userRole ?? 'นักวิจัย' }}
                        </span>
                        <small class="text-body-secondary"><i class="bi bi-lock-fill me-1"></i>เฉพาะผู้ดูแลระบบ</small>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">เชื่อมโยงข้อมูลนักวิจัย (Linked Researcher)</label>
                @if($canManageRoles)
                    <select name="researcher_id" id="researcher_id" class="form-select @error('researcher_id') is-invalid @enderror">
                        <option value="">-- ไม่ระบุ / ไม่ใช่นักวิจัย --</option>
                        @foreach($researchers as $res)
                            <option value="{{ $res->researcher_id }}" {{ old('researcher_id', $user->researcher_id) == $res->researcher_id ? 'selected' : '' }}>
                                {{ $res->prefix->prefix_nameTH ?? '' }}{{ $res->researcher_fname }} {{ $res->researcher_lname }} ({{ $res->department->department_nameTH ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('researcher_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @else
                    <div class="form-control bg-body-tertiary d-flex align-items-center justify-content-between">
                        <span>
                            @if($user->researcher)
                                <i class="bi bi-person-check text-success me-1"></i>{{ $user->researcher->full_name }} ({{ $user->researcher->department->department_nameTH ?? '-' }})
                            @else
                                <span class="text-body-secondary">ไม่ได้เชื่อมโยง</span>
                            @endif
                        </span>
                        <small class="text-body-secondary"><i class="bi bi-lock-fill me-1"></i>เฉพาะผู้ดูแลระบบ</small>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">สถานะการใช้งาน <span class="text-danger">*</span></label>
                @if($canManageRoles)
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="10" {{ old('status', $user->status) == 10 ? 'selected' : '' }}>ใช้งานได้ (Active)</option>
                        <option value="0" {{ old('status', $user->status) == 0 ? 'selected' : '' }}>ระงับการใช้งาน (Blocked)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @else
                    <div class="form-control bg-body-tertiary d-flex align-items-center justify-content-between">
                        <span>
                            @if($user->status == 10)
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>ใช้งานได้ (Active)</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-slash-circle me-1"></i>ระงับการใช้งาน (Blocked)</span>
                            @endif
                        </span>
                        <small class="text-body-secondary"><i class="bi bi-lock-fill me-1"></i>เฉพาะผู้ดูแลระบบ</small>
                    </div>
                @endif
            </div>
        </div>
    </x-form-wrapper>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TomSelect !== 'undefined') {
            if (document.getElementById('role')) {
                new TomSelect('#role', {
                    placeholder: '-- ค้นหาและเลือกบทบาท --',
                    allowEmptyOption: true,
                    plugins: ['clear_button'],
                });
            }

            if (document.getElementById('researcher_id')) {
                new TomSelect('#researcher_id', {
                    placeholder: '-- ค้นหาชื่อนักวิจัย หรือคณะ/หน่วยงาน --',
                    allowEmptyOption: true,
                    maxOptions: 1000,
                    plugins: ['clear_button'],
                });
            }
        }
    });
</script>
@endpush
