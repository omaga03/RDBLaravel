@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header & Navigation --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('backend.rbac.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
                <h3 class="mb-0 fw-bold text-body">
                    <i class="bi bi-shield-lock-fill text-primary me-2"></i>กำหนดสิทธิ์การใช้งาน (Permission Matrix)
                </h3>
            </div>
            <p class="text-body-secondary mb-0">
                บทบาท: <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle fs-6 font-monospace">{{ $item->name }}</span>
                @if($item->description)
                    <span class="ms-2 text-muted">({{ $item->description }})</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" id="btnSelectAll">
                <i class="bi bi-check2-all me-1"></i> เลือกทั้งหมด
            </button>
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" id="btnDeselectAll">
                <i class="bi bi-x-circle me-1"></i> ยกเลิกทั้งหมด
            </button>
            <button type="submit" form="permissionsForm" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm">
                <i class="bi bi-save me-2"></i> บันทึกสิทธิ์
            </button>
        </div>
    </div>

    @if($item->name === 'The Creator')
        <div class="alert alert-info border-info d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
            <div>
                <strong>หมายเหตุสำคัญ:</strong> บทบาท <strong>The Creator</strong> เป็นบทบาทผู้ดูแลระบบสูงสุด (Super Administrator) ซึ่งได้รับสิทธิ์การเข้าถึงและจัดการทุกโมดูลในระบบโดยอัตโนมัติ การปรับแก้สิทธิ์ในหน้านี้จะไม่มีผลกระทบต่อสิทธิ์หลักของ The Creator
            </div>
        </div>
    @endif

    {{-- Form Matrix --}}
    <form id="permissionsForm" action="{{ route('backend.rbac.update_permissions', $item->name) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            @foreach($permissionGroups as $groupKey => $group)
                @php
                    $groupPermNames = array_keys($group['permissions']);
                    $assignedInGroup = array_intersect($groupPermNames, $assignedPermissions);
                    $groupCount = count($assignedInGroup);
                    $totalInGroup = count($groupPermNames);
                    $allChecked = ($groupCount === $totalInGroup);
                @endphp
                <div class="col-lg-6 col-xl-6">
                    <div class="card h-100 shadow-sm border permission-card" data-group="{{ $groupKey }}">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $group['color'] }}-subtle text-{{ $group['color'] }}-emphasis border border-{{ $group['color'] }}-subtle p-2">
                                    <i class="bi {{ $group['icon'] }} fs-5"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0 fw-bold text-body">{{ $group['name'] }}</h6>
                                    <small class="text-body-secondary">{{ $group['description'] }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border group-counter">
                                    {{ $groupCount }}/{{ $totalInGroup }}
                                </span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input group-toggle" type="checkbox" role="switch" 
                                           id="toggle_{{ $groupKey }}" 
                                           data-target="{{ $groupKey }}"
                                           {{ $allChecked ? 'checked' : '' }}
                                           title="เลือก/ยกเลิกทั้งโมดูล">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="list-group list-group-flush">
                                @foreach($group['permissions'] as $permName => $permDetails)
                                    @php
                                        $isChecked = in_array($permName, $assignedPermissions);
                                    @endphp
                                    <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 py-3 px-2 border-0 rounded-2 mb-1" for="perm_{{ md5($permName) }}" style="cursor: pointer;">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input perm-checkbox group-{{ $groupKey }}" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   name="permissions[]" 
                                                   value="{{ $permName }}" 
                                                   id="perm_{{ md5($permName) }}" 
                                                   data-group="{{ $groupKey }}"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-body">{{ $permDetails['label'] }}</span>
                                                <code class="small text-muted">{{ $permName }}</code>
                                            </div>
                                            <div class="small text-body-secondary mt-1">{{ $permDetails['desc'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom Sticky Save Toolbar --}}
        <div class="card shadow-sm border mt-4 bg-body-tertiary">
            <div class="card-body d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
                <div class="text-body-secondary">
                    <i class="bi bi-info-circle me-1"></i> เมื่อปรับแต่งสิทธิ์เสร็จสิ้น อย่าลืมกด <strong>บันทึกสิทธิ์</strong> เพื่ออัปเดตลงฐานข้อมูล
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('backend.rbac.index') }}" class="btn btn-secondary px-4">
                        <i class="bi bi-x-circle me-1"></i> ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i> บันทึกสิทธิ์
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Select All / Deselect All
    const selectAllBtn = document.getElementById('btnSelectAll');
    const deselectAllBtn = document.getElementById('btnDeselectAll');
    const allCheckboxes = document.querySelectorAll('.perm-checkbox');
    const allGroupToggles = document.querySelectorAll('.group-toggle');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            allCheckboxes.forEach(cb => cb.checked = true);
            allGroupToggles.forEach(gt => gt.checked = true);
            updateAllCounters();
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            allCheckboxes.forEach(cb => cb.checked = false);
            allGroupToggles.forEach(gt => gt.checked = false);
            updateAllCounters();
        });
    }

    // 2. Group Toggle Switch
    allGroupToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const groupKey = this.dataset.target;
            const checkboxes = document.querySelectorAll('.group-' + groupKey);
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateGroupCounter(groupKey);
        });
    });

    // 3. Individual Checkbox Change
    allCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const groupKey = this.dataset.group;
            updateGroupCounter(groupKey);
        });
    });

    function updateGroupCounter(groupKey) {
        const card = document.querySelector(`.permission-card[data-group="${groupKey}"]`);
        if (!card) return;

        const checkboxes = card.querySelectorAll('.perm-checkbox');
        const checkedCount = card.querySelectorAll('.perm-checkbox:checked').length;
        const totalCount = checkboxes.length;
        
        const counter = card.querySelector('.group-counter');
        if (counter) {
            counter.textContent = `${checkedCount}/${totalCount}`;
        }

        const groupToggle = card.querySelector('.group-toggle');
        if (groupToggle) {
            groupToggle.checked = (checkedCount === totalCount && totalCount > 0);
        }
    }

    function updateAllCounters() {
        document.querySelectorAll('.permission-card').forEach(card => {
            const groupKey = card.dataset.group;
            if (groupKey) {
                updateGroupCounter(groupKey);
            }
        });
    }
});
</script>
@endpush
