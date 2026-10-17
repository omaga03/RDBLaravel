@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มผู้ใช้งานใหม่ (Add New User)" 
        icon="bi-person-plus-fill"
        mode="create" 
        :actionRoute="route('backend.user.store')" 
        :backRoute="route('backend.user.index')" 
    >
        <div class="row g-3">
            <div class="col-md-6">
                <label for="username" class="form-label fw-bold">ชื่อผู้ใช้ (Username) <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required placeholder="เช่น admin, researcher01">
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-bold">อีเมล (Email) <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label fw-bold">รหัสผ่าน (Password) <span class="text-danger">*</span></label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="อย่างน้อย 8 ตัวอักษร">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="role" class="form-label fw-bold">บทบาท (Role) <span class="text-danger">*</span></label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">-- ค้นหาและเลือกบทบาท --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ old('role') == $r->name ? 'selected' : '' }}>
                            {{ $r->name }} {{ $r->description ? '('.$r->description.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="researcher_id" class="form-label fw-bold">เชื่อมโยงข้อมูลนักวิจัย (Linked Researcher)</label>
                <select name="researcher_id" id="researcher_id" class="form-select @error('researcher_id') is-invalid @enderror">
                    <option value="">-- ไม่ระบุ / ไม่ใช่นักวิจัย --</option>
                    @foreach($researchers as $res)
                        <option value="{{ $res->researcher_id }}" {{ old('researcher_id') == $res->researcher_id ? 'selected' : '' }}>
                            {{ $res->prefix->prefix_nameTH ?? '' }}{{ $res->researcher_fname }} {{ $res->researcher_lname }} ({{ $res->department->department_nameTH ?? '-' }})
                        </option>
                    @endforeach
                </select>
                @error('researcher_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-bold">สถานะการใช้งาน <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="10" {{ old('status', 10) == 10 ? 'selected' : '' }}>ใช้งานได้ (Active)</option>
                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>ระงับการใช้งาน (Blocked)</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-form-wrapper>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TomSelect !== 'undefined') {
            new TomSelect('#role', {
                placeholder: '-- ค้นหาและเลือกบทบาท --',
                allowEmptyOption: true,
                plugins: ['clear_button'],
            });

            new TomSelect('#researcher_id', {
                placeholder: '-- ค้นหาชื่อนักวิจัย หรือคณะ/หน่วยงาน --',
                allowEmptyOption: true,
                maxOptions: 1000,
                plugins: ['clear_button'],
            });
        }
    });
</script>
@endpush
