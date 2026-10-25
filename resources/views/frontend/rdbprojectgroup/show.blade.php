@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('frontend.rdbprojectgroup.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> กลับ</a>

    <div class="card shadow-sm border mb-4">
        <div class="card-body">
            <h4 class="fw-bold">{{ strip_tags((string) $item->pro_nameTH) }}</h4>
            <div class="text-body-secondary mb-2">
                ปีงบประมาณ {{ $item->year->year_name ?? '-' }} · {{ $item->department->department_nameTH ?? '-' }}
                @if($item->type) · {{ $item->type->pt_name }} @endif
            </div>
            <div class="fs-5 text-success fw-bold">งบประมาณรวมแผนงาน ฿{{ number_format($totalBudget, 2) }}</div>
            <a href="{{ route('frontend.rdbproject.show', $item->pro_id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-2">ดูรายละเอียดโครงการหลัก</a>
        </div>
    </div>

    <div class="card shadow-sm border">
        <div class="card-header bg-transparent fw-bold">โครงการย่อย ({{ $subProjects->count() }})</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>โครงการ</th><th>ผู้วิจัย</th><th class="text-center">สถานะ</th><th class="text-end">งบประมาณ</th></tr></thead>
                <tbody>
                    @forelse($subProjects as $sp)
                    @php $lead = $sp->rdbProjectWorks->first(); @endphp
                    <tr>
                        <td><a href="{{ route('frontend.rdbproject.show', $sp->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body fw-bold">{{ strip_tags((string) $sp->pro_nameTH) }}</a></td>
                        <td>{{ $lead && $lead->researcher ? ($lead->researcher->prefix->prefix_nameTH ?? '').$lead->researcher->researcher_fname.' '.$lead->researcher->researcher_lname : '-' }}</td>
                        <td class="text-center">{{ $sp->status->ps_name ?? '-' }}</td>
                        <td class="text-end">฿{{ number_format($sp->pro_budget, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-3 text-body-secondary">ไม่มีโครงการย่อย</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
