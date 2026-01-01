@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-journal-text me-2"></i>จัดการข้อมูลการตีพิมพ์ (Publications)</h2>
        <a href="{{ route('backend.rdb_published.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข้อมูลตีพิมพ์
        </a>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.rdb_published.index')" :collapsed="true">
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

    {{-- Data Table --}}
    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-top mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%;">ชื่อผลงาน</th>
                        <th style="width: 25%;">ประเภท</th>
                        <th style="width: 25%;">คณะผู้เขียน</th>
                        <th style="width: 10%;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold">
                                {{ $item->pub_name }}
                            </div>
                            <div class="text-muted small mb-1">
                                <i class="bi bi-calendar-event me-1"></i> {{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}
                            </div>
                            <small class="text-secondary d-block">
                                <i class="bi bi-journal-bookmark me-1"></i> {{ $item->pub_name_journal }}
                            </small>
                        </td>
                        <td>
                            <i class="bi bi-tag me-1 text-secondary"></i> {{ $item->pubtype->pubtype_subgroup ?? $item->pubtype->pubtype_grouptype ?? '-' }}
                        </td>
                        <td>
                            @if($item->authors->isNotEmpty())
                                @php
                                    $firstAuthor = $item->authors->first();
                                    $remainingCount = $item->authors->count() - 1;
                                @endphp
                                <div>
                                    <i class="bi bi-person-circle text-secondary"></i>
                                    {{ $firstAuthor->researcher_fname }} {{ $firstAuthor->researcher_lname }}
                                    @if(isset($authorTypes[$firstAuthor->pivot->pubta_id]))
                                        <small class="text-muted">({{ $authorTypes[$firstAuthor->pivot->pubta_id] }})</small>
                                    @endif
                                    @if($remainingCount > 0)
                                        <span class="text-muted small">(+{{ $remainingCount }})</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdb_published.show', $item->getKey()) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            ไม่พบข้อมูล
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($items->hasPages())
        <div class="mt-3">
            {{ $items->withQueryString()->links() }}
        </div>
        @endif
    </x-card>
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
