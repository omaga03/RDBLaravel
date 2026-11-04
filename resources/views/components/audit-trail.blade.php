@props([
    'auditable' => null,
    'title' => 'ประวัติกิจกรรมและการแก้ไข (Activity & Audit Trail)',
    'limit' => 50,
    'initialCount' => 5,
])

@php
    if (!$auditable || !auth()->check() || !method_exists($auditable, 'canViewAuditTrail')) {
        return;
    }

    if (!$auditable->canViewAuditTrail(auth()->user())) {
        return;
    }

    $allLogs = $auditable->auditLogs()->take($limit)->get();
    $totalCount = $allLogs->count();
    $recentLogs = $allLogs->take($initialCount);
    $remainingLogs = $allLogs->slice($initialCount);

    $isPrivileged = auth()->user()->hasRole(['The Creator', 'Admin', 'Admin Research', 'Admin Project']) || (int)auth()->id() === 129;
    $auditableType = class_basename($auditable);
    $auditableId = $auditable->getKey();
@endphp

<div class="card shadow-none border border-subtle rounded-3 mt-3 mb-4" id="recordAuditTrailCard" style="font-size: 0.82rem;">
    <!-- Compact Header (Subtle & Unobtrusive) -->
    <div class="card-header bg-body-tertiary py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-body-secondary fs-6"></i>
            <span class="fw-semibold text-body" style="font-size: 0.85rem;">{{ $title }}</span>
            <span class="badge bg-secondary-subtle text-body-secondary border tabular-nums" style="font-size: 0.68rem;">
                #{{ $auditableId }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-body-secondary" style="font-size: 0.74rem;">
                ทั้งหมด <span class="fw-bold tabular-nums text-body">{{ $totalCount }}</span> รายการ
            </span>
            @if($isPrivileged && Route::has('backend.audit_log.index'))
                <a href="{{ route('backend.audit_log.index', ['auditable_type' => $auditableType, 'search' => $auditableId]) }}" 
                   class="btn btn-outline-secondary btn-xs py-0.5 px-2 rounded d-inline-flex align-items-center gap-1 shadow-none" 
                   style="font-size: 0.72rem;"
                   target="_blank" 
                   title="ดูในศูนย์ Audit Log รวมของระบบ">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span class="d-none d-sm-inline">ศูนย์บันทึก</span>
                </a>
            @endif
        </div>
    </div>

    <div class="card-body p-2.5 p-md-3">
        @if($allLogs->isEmpty())
            <div class="text-center py-3 text-body-secondary">
                <i class="bi bi-clock-history fs-4 text-secondary-subtle d-block mb-1"></i>
                <div style="font-size: 0.8rem;">ยังไม่มีประวัติกิจกรรมที่บันทึกไว้สำหรับรายการนี้</div>
                <small class="text-body-tertiary" style="font-size: 0.72rem;">ระบบจะบันทึกอัตโนมัติเมื่อมีการสร้าง แก้ไข หรือเปลี่ยนสถานะ</small>
            </div>
        @else
            <!-- Editorial Timeline List -->
            <div class="audit-timeline position-relative ps-3 ps-md-3.5">
                <div class="position-absolute top-0 bottom-0 start-0 border-start border-secondary-subtle" style="left: 10px !important;"></div>

                @foreach($allLogs as $index => $log)
                    @if($index === $initialCount)
                        <div class="collapse" id="auditTrailMoreCollapse_{{ $auditableId }}">
                    @endif

                    @php
                        $eventColor = match($log->event) {
                            'create' => ['badge' => 'bg-success-subtle text-success-emphasis border-success-subtle', 'icon' => 'bi-plus-circle-fill text-success'],
                            'update' => ['badge' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle', 'icon' => 'bi-pencil-square text-primary'],
                            'delete' => ['badge' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle', 'icon' => 'bi-trash3-fill text-danger'],
                            default => ['badge' => 'bg-info-subtle text-info-emphasis border-info-subtle', 'icon' => 'bi-arrow-repeat text-info']
                        };

                        $old = $log->old_values ?? [];
                        $new = $log->new_values ?? [];
                        $changedKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                        $collapseId = 'auditDiff_' . $log->id . '_' . $index;

                        $formatVal = function($v) {
                            if (is_null($v)) return '<span class="text-body-tertiary fst-italic">null</span>';
                            if (is_bool($v)) return $v ? 'true' : 'false';
                            if (is_array($v)) return '<pre class="mb-0 text-wrap font-monospace" style="font-size: 0.7rem;">' . htmlspecialchars(json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
                            $str = (string) $v;
                            if (strlen($str) > 160) {
                                return htmlspecialchars(mb_substr($str, 0, 160)) . '... <small class="text-body-secondary">(' . strlen($str) . ' chars)</small>';
                            }
                            return htmlspecialchars($str);
                        };
                    @endphp

                    <div class="audit-item position-relative pb-2.5 mb-2.5 border-bottom border-light-subtle">
                        <!-- Bullet Icon on Timeline -->
                        <div class="position-absolute bg-body rounded-circle d-flex align-items-center justify-content-center" 
                             style="left: -19px; top: 2px; width: 18px; height: 18px; z-index: 2;">
                            <i class="bi {{ $eventColor['icon'] }}" style="font-size: 0.72rem;"></i>
                        </div>

                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-1.5 mb-1">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge {{ $eventColor['badge'] }} border text-uppercase" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                    {{ $log->event }}
                                </span>
                                <span class="fw-semibold text-body" style="font-size: 0.82rem;">
                                    {{ $log->user_name ?? 'ระบบอัตโนมัติ' }}
                                </span>
                                @if($log->user_role)
                                    <span class="badge bg-body-secondary text-body-secondary border" style="font-size: 0.62rem; padding: 0.15rem 0.35rem;">
                                        {{ $log->user_role }}
                                    </span>
                                @endif
                                @if($log->scope_info)
                                    <span class="text-body-tertiary" style="font-size: 0.7rem;">
                                        ({{ $log->scope_info }})
                                    </span>
                                @endif
                            </div>

                            <div class="text-body-secondary d-flex align-items-center gap-1.5" style="font-size: 0.74rem;">
                                <span class="tabular-nums" title="{{ $log->created_at }}">
                                    <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::formatDateTime($log->created_at) }}
                                </span>
                                <span class="badge bg-body-tertiary text-body-tertiary border" style="font-size: 0.64rem;">
                                    {{ $log->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Summary & Remarks -->
                        <div class="ps-1">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5">
                                <div class="text-body" style="font-size: 0.78rem;">
                                    <span class="fw-medium text-body-emphasis">{{ $log->remarks ?: 'บันทึกการทำงาน' }}</span>
                                    @if($log->diff_summary)
                                        <span class="text-body-secondary ms-1">— {{ $log->diff_summary }}</span>
                                    @endif
                                </div>

                                @if(!empty($changedKeys))
                                    <button type="button" 
                                            class="btn btn-xs btn-outline-secondary py-0 px-1.5 rounded-pill d-inline-flex align-items-center gap-1 shadow-none" 
                                            style="font-size: 0.7rem; border-color: var(--bs-border-color);"
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#{{ $collapseId }}" 
                                            aria-expanded="false" 
                                            aria-controls="{{ $collapseId }}">
                                        <i class="bi bi-file-diff"></i>
                                        <span>ดูที่เปลี่ยน ({{ count($changedKeys) }})</span>
                                    </button>
                                @endif
                            </div>

                            <!-- Admin Technical Metadata Pills (Privileged View Only) -->
                            @if($isPrivileged && ($log->ip_address || $log->user_agent))
                                <div class="mt-1 d-flex align-items-center gap-1.5 flex-wrap" style="font-size: 0.68rem;">
                                    @if($log->ip_address)
                                        <span class="badge bg-body-tertiary text-body-secondary border font-monospace">
                                            IP: {{ $log->ip_address }}
                                        </span>
                                    @endif
                                    @if($log->user_agent)
                                        <span class="badge bg-body-tertiary text-body-secondary border font-monospace text-truncate" style="max-width: 280px;" title="{{ $log->user_agent }}">
                                            UA: {{ Str::limit($log->user_agent, 35) }}
                                        </span>
                                    @endif
                                    @if($log->url)
                                        <span class="badge bg-body-tertiary text-body-secondary border font-monospace text-truncate" style="max-width: 200px;" title="{{ $log->url }}">
                                            URI: {{ parse_url($log->url, PHP_URL_PATH) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Inline Diff Accordion -->
                            @if(!empty($changedKeys))
                                <div class="collapse mt-2" id="{{ $collapseId }}">
                                    <div class="card card-body p-2 bg-body-tertiary border rounded-2 shadow-none">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-body mb-0" style="font-size: 0.74rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 25%;">ฟิลด์</th>
                                                        <th style="width: 37.5%;" class="text-danger-emphasis">ค่าเดิม</th>
                                                        <th style="width: 37.5%;" class="text-success-emphasis">ค่าใหม่</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($changedKeys as $fieldKey)
                                                        @php
                                                            $oldVal = $old[$fieldKey] ?? null;
                                                            $newVal = $new[$fieldKey] ?? null;
                                                        @endphp
                                                        <tr>
                                                            <td class="font-monospace fw-semibold text-body">
                                                                {{ $fieldKey }}
                                                            </td>
                                                            <td class="text-break bg-danger-subtle bg-opacity-25">
                                                                {!! $formatVal($oldVal) !!}
                                                            </td>
                                                            <td class="text-break bg-success-subtle bg-opacity-25">
                                                                {!! $formatVal($newVal) !!}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($totalCount > $initialCount)
                    </div>
                @endif
            </div>

            <!-- Expand / Collapse All Logs Button -->
            @if($totalCount > $initialCount)
                <div class="text-center pt-2">
                    <button type="button" 
                            class="btn btn-outline-secondary btn-xs py-1 px-3 rounded-pill d-inline-flex align-items-center gap-1.5 shadow-none"
                            style="font-size: 0.76rem;"
                            data-bs-toggle="collapse" 
                            data-bs-target="#auditTrailMoreCollapse_{{ $auditableId }}"
                            aria-expanded="false"
                            aria-controls="auditTrailMoreCollapse_{{ $auditableId }}"
                            id="btnToggleAuditMore_{{ $auditableId }}"
                            onclick="
                                const isExp = this.getAttribute('aria-expanded') === 'true';
                                this.querySelector('.audit-toggle-label').innerText = isExp ? 'ย่อเหลือ {{ $initialCount }} รายการล่าสุด' : 'ดูประวัติทั้งหมด (อีก {{ $totalCount - $initialCount }} รายการ)';
                                const icon = this.querySelector('i');
                                icon.className = isExp ? 'bi bi-chevron-up me-0.5' : 'bi bi-chevron-down me-0.5';
                            ">
                        <i class="bi bi-chevron-down me-0.5"></i>
                        <span class="audit-toggle-label">ดูประวัติทั้งหมด (อีก {{ $totalCount - $initialCount }} รายการ)</span>
                    </button>
                </div>
            @endif
        @endif
    </div>
</div>
