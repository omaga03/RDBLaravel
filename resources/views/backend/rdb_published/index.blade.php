@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-journal-text me-2"></i>จัดการข้อมูลการตีพิมพ์ (Publications)</h2>
        <a href="{{ route('backend.rdb_published.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข้อมูลตีพิมพ์
        </a>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.rdb_published.index')" :exportRoute="route('backend.rdb_published.export')" :collapsed="true">
        <div class="row g-3">
            {{-- Keywords --}}
            <div class="col-md-6">
                <label class="form-label">คำค้นหา (Keywords)</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="พิมพ์ชื่อผลงาน, ชื่อวารสาร, หรือชื่อนักวิจัย...">
            </div>

            {{-- Year Logic --}}
            <div class="col-md-3">
                <label class="form-label">ประเภทปี</label>
                <select class="form-select" name="year_type" id="s_year_type">
                    <option value="">-- เลือกประเภทปี --</option>
                    <option value="calendar" {{ request('year_type') == 'calendar' ? 'selected' : '' }}>ปี พ.ศ. (ตีพิมพ์)</option>
                    <option value="budget" {{ request('year_type') == 'budget' ? 'selected' : '' }}>ปีงบประมาณ</option>
                    <option value="education" {{ request('year_type') == 'education' ? 'selected' : '' }}>ปีการศึกษา</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">เลือกปี</label>
                <select class="form-select" name="year_id" id="s_year_id" disabled>
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($years as $year)
                        <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Type Logic --}}
            <div class="col-md-4">
                <label class="form-label">กลุ่มประเภท (Group)</label>
                <select class="form-select" id="s_pubtype_group" name="pubtype_group">
                    <option value="">-- ทั้งหมด --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">ประเภทหลัก (Type)</label>
                <select class="form-select" id="s_pubtype_grouptype" name="pubtype_grouptype" disabled>
                    <option value="">-- ทั้งหมด --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">ประเภทย่อย (Subtype)</label>
                <select class="form-select" id="s_pubtype_id" name="pubtype_id" disabled>
                    <option value="">-- ทั้งหมด --</option>
                </select>
            </div>

            {{-- Department / Branch --}}
            <div class="col-md-6">
                <label class="form-label">หน่วยงาน/คณะ</label>
                <select class="form-select" name="department_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">สาขาทางวิชาการ</label>
                <select class="form-select" name="branch_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($branches as $br)
                        <option value="{{ $br->branch_id }}" {{ request('branch_id') == $br->branch_id ? 'selected' : '' }}>{{ $br->branch_name }}</option>
                    @endforeach
                </select>
            </div>

             {{-- Score / Budget --}}
             <div class="col-md-4">
                <label class="form-label">คะแนน (Score)</label>
                <select class="form-select" name="pub_score">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach(['0.00', '0.20', '0.40', '0.60', '0.80', '1.00'] as $score)
                        <option value="{{ $score }}" {{ request('pub_score') == $score ? 'selected' : '' }}>{{ $score }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">งบประมาณเริ่มต้น</label>
                <input type="number" step="0.01" class="form-control" name="budget_min" value="{{ request('budget_min') }}" placeholder="ต่ำสุด">
            </div>
            <div class="col-md-4">
                <label class="form-label">งบประมาณสิ้นสุด</label>
                <input type="number" step="0.01" class="form-control" name="budget_max" value="{{ request('budget_max') }}" placeholder="สูงสุด">
            </div>

            {{-- Date Range --}}
            <div class="col-md-6">
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="date" class="form-control datepicker" id="s_date_start" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" class="form-control datepicker" id="s_date_end" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการข้อมูลการตีพิมพ์
                <span class="badge bg-primary rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">ชื่อผลงาน</th>
                            <th style="width: 20%;">ประเภท</th>
                            <th style="width: 25%;">คณะผู้เขียน</th>
                            <th style="width: 10%;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="p-3">
                                <div class="fw-bold mb-1">
                                    <a href="{{ route('backend.rdb_published.show', $item->getKey()) }}" class="text-decoration-none text-body fw-bold hover-primary text-break">
                                        {{ $item->pub_name }}
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 small">
                                    @if(isset($item->data_show))
                                        @if($item->data_show)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="เผยแพร่สู่สาธารณะ">
                                                <i class="bi bi-eye"></i> สาธารณะ
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="ซ่อนไว้ (ไม่แสดงหน้าบ้าน)">
                                                <i class="bi bi-eye-slash"></i> ซ่อน
                                            </span>
                                        @endif
                                    @endif
                                    @if($item->pub_date)
                                        <span class="text-body-secondary">
                                            <i class="bi bi-calendar-event me-1"></i> {{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}
                                        </span>
                                    @endif
                                </div>
                                @if($item->pub_name_journal)
                                <small class="text-body-secondary d-block mt-1 text-break">
                                    <i class="bi bi-journal-bookmark me-1 text-primary"></i> {{ $item->pub_name_journal }}
                                </small>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle text-wrap text-start">
                                    {{ $item->pubtype->pubtype_subgroup ?? $item->pubtype->pubtype_grouptype ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($item->authors->isNotEmpty())
                                    @php
                                        $firstAuthor = $item->authors->first();
                                        $remainingCount = $item->authors->count() - 1;
                                    @endphp
                                    <div>
                                        <i class="bi bi-person-circle text-primary me-1"></i>
                                        <a href="{{ route('backend.rdb_researcher.show', $firstAuthor->researcher_id) }}" class="text-decoration-none text-body fw-bold hover-primary">
                                            {{ $firstAuthor->researcher_fname }} {{ $firstAuthor->researcher_lname }}
                                        </a>
                                        @if(isset($authorTypes[$firstAuthor->pivot->pubta_id]))
                                            <small class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.75rem;">
                                                {{ $authorTypes[$firstAuthor->pivot->pubta_id] }}
                                            </small>
                                        @endif
                                        @if($remainingCount > 0)
                                            <span class="text-body-secondary small ms-1">(+{{ $remainingCount }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-body-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center p-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.rdb_published.show', $item->getKey()) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdb_published.edit', $item->getKey()) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                ไม่พบข้อมูลการตีพิมพ์
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($items->hasPages())
            <div class="d-flex justify-content-center p-4">
                {{ $items->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 0. Year Logic
    const sYearType = document.getElementById('s_year_type');
    const sYearId = document.getElementById('s_year_id');

    if (sYearType && sYearId) {
        // Init state
        if (sYearType.value) {
            sYearId.disabled = false;
        }

        sYearType.addEventListener('change', function() {
            if (this.value) {
                sYearId.disabled = false;
            } else {
                sYearId.disabled = true;
                sYearId.value = '';
            }
        });
    }

    // 1. PubType Logic
    const pubTypes = @json($pubTypes);
    
    // 2. Datepicker Logic (Thai)
    if (typeof initThaiFlatpickr === 'function') {
        initThaiFlatpickr('#s_date_start', {
            onChange: function(selectedDates, dateStr, instance) {
                const endInput = document.getElementById('s_date_end');
                if (endInput && endInput._flatpickr) {
                    endInput._flatpickr.set('minDate', selectedDates[0] || null);
                }
            }
        });

        initThaiFlatpickr('#s_date_end', {
             onReady: function(selectedDates, dateStr, instance) {
                 const startInput = document.getElementById('s_date_start');
                 if (startInput && startInput._flatpickr && startInput._flatpickr.selectedDates.length > 0) {
                     instance.set('minDate', startInput._flatpickr.selectedDates[0]);
                 }
             }
        });
    }

    // Select Elements
    const sGroup = document.getElementById('s_pubtype_group');
    const sType = document.getElementById('s_pubtype_grouptype');
    const sSub = document.getElementById('s_pubtype_id');

    // Values from Request
    const oldGroup = "{{ request('pubtype_group') }}";
    const oldType = "{{ request('pubtype_grouptype') }}";
    const oldSub = "{{ request('pubtype_id') }}";

    // Extract Unique Groups
    const uniqueGroups = [...new Set(pubTypes.map(item => item.pubtype_group))].filter(Boolean);
    uniqueGroups.forEach(g => {
        sGroup.add(new Option(g, g));
    });

    sGroup.addEventListener('change', function() {
        const val = this.value;
        sType.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        sSub.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        sSub.disabled = true;

        if (val) {
             const filteredInfo = pubTypes.filter(item => item.pubtype_group === val);
             const subTypes = [...new Set(filteredInfo.map(item => item.pubtype_grouptype))].filter(Boolean);
             subTypes.forEach(t => {
                 sType.add(new Option(t, t));
             });
             sType.disabled = false;
        } else {
            sType.disabled = true;
        }
    });

    sType.addEventListener('change', function() {
        const gVal = sGroup.value;
        const val = this.value;
        sSub.innerHTML = '<option value="">-- ทั้งหมด --</option>';

        if (val && gVal) {
             const filteredInfo = pubTypes.filter(item => item.pubtype_group === gVal && item.pubtype_grouptype === val);
             filteredInfo.forEach(item => {
                 sSub.add(new Option(item.pubtype_subgroup || item.pubtype_grouptype, item.pubtype_id));
             });
             sSub.disabled = false;
        } else {
            sSub.disabled = true;
        }
    });

    // Init Values
    if (oldGroup) {
        sGroup.value = oldGroup;
        sGroup.dispatchEvent(new Event('change'));
        if (oldType) {
            sType.value = oldType;
            sType.dispatchEvent(new Event('change'));
            if (oldSub) {
                sSub.value = oldSub;
            }
        }
    }
});
</script>
@endpush
@endsection
