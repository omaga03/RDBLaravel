@props(['delta' => null, 'prevYearName' => null, 'unit' => '', 'minBase' => 5, 'isCurrency' => false, 'prefix' => ''])

@php
    $prevYearName = $prevYearName ?? ($yoyDelta['prev_year_name'] ?? null);
    $prev = is_array($delta) ? ($delta['prev'] ?? null) : null;
    $diff = is_array($delta) ? ($delta['diff'] ?? null) : null;
    $pct  = is_array($delta) ? ($delta['pct'] ?? null) : null;
@endphp

@if(!empty($prevYearName) && $prev !== null)
    <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
        <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $prevYearName }}</span>
        @if($prev >= $minBase && $pct !== null)
            <span class="badge {{ $diff >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                <i class="bi {{ $diff >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                {{ $diff >= 0 ? '+' : '' }}{{ $pct }}%
            </span>
        @else
            <span class="text-body-secondary fw-semibold text-truncate ms-1" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;" title="ปีก่อนหน้า {{ $isCurrency ? '฿' . number_format($prev) : $prefix . number_format($prev, is_float($prev) && floor($prev) != $prev ? 2 : 0) . ' ' . $unit }}">
                ปีก่อน {{ $isCurrency ? '฿' . number_format($prev) : $prefix . number_format($prev, is_float($prev) && floor($prev) != $prev ? 2 : 0) }} {{ $unit }}
            </span>
        @endif
    </div>
@endif
