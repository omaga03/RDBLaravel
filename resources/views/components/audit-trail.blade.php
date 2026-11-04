@props([
    'auditable' => null,
    'title' => 'ประวัติกิจกรรมและการแก้ไข (Activity & Audit Trail)',
    'limit' => 25,
])

@php
    if (!$auditable || !auth()->check() || !method_exists($auditable, 'canViewAuditTrail')) {
        return;
    }

    if (!$auditable->canViewAuditTrail(auth()->user())) {
        return;
    }

    $logs = $auditable->auditLogs()->take($limit)->get();
    $isPrivileged = auth()->user()->hasRole(['The Creator', 'Admin', 'Admin Research', 'Admin Project']) || (int)auth()->id() === 129;
    $auditableType = class_basename($auditable);
    $auditableId = $auditable->getKey();
@endphp

<div class="card shadow-sm border rounded-3 mt-4 mb-4" id="recordAuditTrailCard">
    <div class="card-header bg-body-tertiary py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-2 bg-primary-subtle text-primary border border-primary-subtle flex-shrink-0">
                <i class="bi bi-clock-history fs-5"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-body">{{ $title }}</h6>
                <small class="text-body-secondary">
                    รหัสรายการ: <span class="fw-semibold text-body">#{{ $auditableId }}</span> ({{ $auditableType }})
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary-subtle text-body-secondary border">
                <i class="bi bi-list-nested me-1"></i>{{ $logs->count() }} รายการบันทึก
            </span>
            @if($isPrivileged && Route::has('backend.audit_log.index'))
                <a href="{{ route('backend.audit_log.index', ['auditable_type' => $auditableType, 'search' => $auditableId]) }}" 
                   class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" 
                   target="_blank" 
                   title="ดูในศูนย์บันทึกกิจกรรมระบบทั้งหมด">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span class="d-none d-sm-inline">ศูนย์ Audit Log</span>
                </a>
            @endif
        </div>
    </div>

    <div class="card-body p-3 p-md-4">
        @if($logs->isEmpty())
            <div class="text-center py-4 text-body-secondary">
                <i class="bi bi-clock-history fs-2 text-secondary-subtle d-block mb-2"></i>
                <div class="fw-medium">ยังไม่มีประวัติกิจกรรมที่บันทึกไว้สำหรับรายการนี้</div>
                <small class="text-body-tertiary">ระบบจะบันทึกประวัติอัตโนมัติเมื่อมีการสร้าง แก้ไข หรือปรับเปลี่ยนสถานะ</small>
            </div>
        @else
            <!-- Editorial Timeline List -->
            <div class="audit-timeline position-relative ps-3 ps-md-4">
                <div class="position-absolute top-0 bottom-0 start-0 border-start border-2 border-secondary-subtle" style="left: 14px !important;"></div>

                @foreach($logs as $index => $log)
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
                    @endphp

                    <div class="audit-item position-relative pb-3 mb-3 border-bottom border-light-subtle">
                        <!-- Bullet Icon on Timeline -->
                        <div class="position-absolute bg-body rounded-circle d-flex align-items-center justify-content-center" 
                             style="left: -22px; top: 2px; width: 22px; height: 22px; z-index: 2;">
                            <i class="bi {{ $eventColor['icon'] }} fs-6"></i>
                        </div>

                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-1.5">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge {{ $eventColor['badge'] }} border text-uppercase" style="font-size: 0.72rem;">
                                    {{ $log->event }}
                                </span>
                                <span class="fw-bold text-body" style="font-size: 0.92rem;">
                                    {{ $log->user_name ?? 'ระบบอัตโนมัติ' }}
                                </span>
                                @if($log->user_role)
                                    <span class="badge bg-body-secondary text-body-secondary border" style="font-size: 0.68rem;">
                                        {{ $log->user_role }}
                                    </span>
                                @endif
                                @if($log->scope_info)
                                    <span class="text-body-tertiary small" style="font-size: 0.75rem;">
                                        ({{ $log->scope_info }})
                                    </span>
                                @endif
                            </div>

                            <div class="text-body-secondary small d-flex align-items-center gap-2">
                                <span class="tabular-nums" title="{{ $log->created_at }}">
                                    <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::formatDateTime($log->created_at) }}
                                </span>
                                <span class="badge bg-body-tertiary text-body-tertiary border" style="font-size: 0.7rem;">
                                    {{ $log->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Summary & Remarks -->
                        <div class="ps-1">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="text-body small">
                                    <span class="fw-semibold text-body-emphasis">{{ $log->remarks ?: 'บันทึกการทำงาน' }}</span>
                                    @if($log->diff_summary)
                                        <span class="text-body-secondary ms-1">— {{ $log->diff_summary }}</span>
                                    @endif
                                </div>

                                @if(!empty($changedKeys))
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm" 
                                            style="font-size: 0.76rem;"
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#{{ $collapseId }}" 
                                            aria-expanded="false" 
                                            aria-controls="{{ $collapseId }}">
                                        <i class="bi bi-file-diff"></i>
                                        <span>ดูรายละเอียดที่เปลี่ยน ({{ count($changedKeys) }} ฟิลด์)</span>
                                    </button>
                                @endif
                            </div>

                            <!-- Admin Technical Metadata Pills (Privileged View Only) -->
                            @if($isPrivileged && ($log->ip_address || $log->user_agent))
                                <div class="mt-1.5 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.74rem;">
                                    @if($log->ip_address)
                                        <span class="badge bg-body-tertiary text-body-secondary border font-monospace">
                                            IP: {{ $log->ip_address }}
                                        </span>
                                    @endif
                                    @if($log->user_agent)
                                        <span class="badge bg-body-tertiary text-body-secondary border font-monospace text-truncate" style="max-width: 320px;" title="{{ $log->user_agent }}">
                                            UA: {{ Str::limit($log->user_agent, 45) }}
                                        </span>
                                    @endif
                                    @if($log->url)
                                        <span class="badge bg-body-tertiary text-body-secondary border font-monospace text-truncate" style="max-width: 250px;" title="{{ $log->url }}">
                                            URI: {{ parse_url($log->url, PHP_URL_PATH) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Inline Diff Accordion -->
                            @if(!empty($changedKeys))
                                <div class="collapse mt-2.5" id="{{ $collapseId }}">
                                    <div class="card card-body p-2.5 bg-body-tertiary border rounded-3 shadow-none">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-body mb-0" style="font-size: 0.8rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 25%;">ชื่อฟิลด์ (Field)</th>
                                                        <th style="width: 37.5%;" class="text-danger-emphasis">ค่าเดิม (Old Value)</th>
                                                        <th style="width: 37.5%;" class="text-success-emphasis">ค่าใหม่ (New Value)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($changedKeys as $fieldKey)
                                                        @php
                                                            $oldVal = $old[$fieldKey] ?? null;
                                                            $newVal = $new[$fieldKey] ?? null;
                                                            $formatVal = function($v) {
                                                                if (is_null($v)) return '<span class="text-body-tertiary fst-italic">null</span>';
                                                                if (is_bool($v)) return $v ? 'true' : 'false';
                                                                if (is_array($v)) return '<pre class="mb-0 text-wrap font-monospace" style="font-size: 0.74rem;">' . htmlspecialchars(json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
                                                                $str = (string) $v;
                                                                if (strlen($str) > 200) {
                                                                    return htmlspecialchars(mb_substr($str, 0, 200)) . '... <small class="text-body-secondary">(' . strlen($str) . ' chars)</small>';
                                                                }
                                                                return htmlspecialchars($str);
                                                            };
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
            </div>
        @endif
    </div>
</div>
