@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header title="คิวงานวิจัยรอการตรวจสอบความถูกต้อง (Review Queue)" description="สำหรับผู้ประสานงานระดับคณะและผู้ดูแลงานเฉพาะด้าน ในการตรวจสอบและอนุมัติเผยแพร่สู่สาธารณะ">
        <a href="{{ route('backend.site.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> กลับหน้าหลัก
        </a>
    </x-page-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Nav Tabs -->
    <ul class="nav nav-pills gap-2 mb-4 p-1.5 bg-body shadow-sm rounded-3 border" id="workflowTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'projects' ? 'active' : '' }}" href="{{ route('backend.workflow.queue', ['tab' => 'projects']) }}">
                <i class="bi bi-folder me-1"></i> โครงการวิจัย
                <span class="badge {{ $projects->total() > 0 ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-secondary' }} ms-1">{{ $projects->total() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'publications' ? 'active' : '' }}" href="{{ route('backend.workflow.queue', ['tab' => 'publications']) }}">
                <i class="bi bi-journal-text me-1"></i> ผลงานตีพิมพ์
                <span class="badge {{ $publications->total() > 0 ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-secondary' }} ms-1">{{ $publications->total() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'dips' ? 'active' : '' }}" href="{{ route('backend.workflow.queue', ['tab' => 'dips']) }}">
                <i class="bi bi-award me-1"></i> ทรัพย์สินทางปัญญา
                <span class="badge {{ $dips->total() > 0 ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-secondary' }} ms-1">{{ $dips->total() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'utilizes' ? 'active' : '' }}" href="{{ route('backend.workflow.queue', ['tab' => 'utilizes']) }}">
                <i class="bi bi-arrow-up-right-circle me-1"></i> การใช้ประโยชน์
                <span class="badge {{ $utilizes->total() > 0 ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-secondary' }} ms-1">{{ $utilizes->total() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'unlocks' ? 'active' : '' }}" href="{{ route('backend.workflow.queue', ['tab' => 'unlocks']) }}">
                <i class="bi bi-unlock me-1"></i> คำขอเปิดสิทธิ์แก้ไข
                <span class="badge {{ $totalUnlocks > 0 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle ' : 'bg-secondary' }} ms-1">{{ $totalUnlocks }}</span>
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            @if($activeTab === 'projects')
                <!-- Projects Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-3" style="width: 140px;">ส่งตรวจเมื่อ</th>
                                <th>ชื่อโครงการวิจัย</th>
                                <th>คณะ / หน่วยงาน</th>
                                <th>นักวิจัย</th>
                                <th class="text-end pe-3" style="width: 220px;">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $p)
                                <tr>
                                    <td class="ps-3 text-muted small">
                                        {{ $p->submitted_at ? \Carbon\Carbon::parse($p->submitted_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('backend.rdb_project.show', $p->pro_id) }}" class="fw-bold text-body text-decoration-none hover-primary">
                                            {{ $p->pro_nameTH }}
                                        </a>
                                        @if($p->pro_code)
                                            <div class="text-muted small">รหัส: {{ $p->pro_code }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $p->department->department_nameTH ?? '-' }}</td>
                                    <td>{{ $p->researchers->first()?->full_name ?? '-' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="{{ route('backend.rdb_project.show', $p->pro_id) }}" class="btn btn-sm btn-outline-secondary" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <!-- Approve Form -->
                                            <form action="{{ route('backend.workflow.approve', ['type' => 'project', 'id' => $p->pro_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติและเผยแพร่โครงการวิจัยนี้สู่สาธารณะ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success text-white" title="อนุมัติเผยแพร่">
                                                    <i class="bi bi-check-lg me-1"></i> อนุมัติ
                                                </button>
                                            </form>
                                            <!-- Reject Trigger Modal -->
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" data-type="project" data-id="{{ $p->pro_id }}" data-title="{{ $p->pro_nameTH }}">
                                                <i class="bi bi-x-lg me-1"></i> ส่งกลับ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                        ไม่มีโครงการวิจัยที่ค้างรอการตรวจสอบในขณะนี้
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($projects->hasPages())
                    <div class="p-3 border-top">{{ $projects->links() }}</div>
                @endif

            @elseif($activeTab === 'publications')
                <!-- Publications Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-3" style="width: 140px;">ส่งตรวจเมื่อ</th>
                                <th>ชื่อบทความ / ผลงานตีพิมพ์</th>
                                <th>ประเภทผลงาน</th>
                                <th>นักวิจัย</th>
                                <th class="text-end pe-3" style="width: 220px;">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($publications as $pub)
                                <tr>
                                    <td class="ps-3 text-muted small">
                                        {{ $pub->submitted_at ? \Carbon\Carbon::parse($pub->submitted_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('backend.rdb_published.show', $pub->id) }}" class="fw-bold text-body text-decoration-none hover-primary">
                                            {{ $pub->pub_name }}
                                        </a>
                                        @if($pub->pub_name_journal)
                                            <div class="text-muted small">วารสาร: {{ $pub->pub_name_journal }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $pub->pubtype->pubtype_name ?? '-' }}</span></td>
                                    <td>{{ $pub->researcher?->full_name ?? '-' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="{{ route('backend.rdb_published.show', $pub->id) }}" class="btn btn-sm btn-outline-secondary" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('backend.workflow.approve', ['type' => 'published', 'id' => $pub->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติและเผยแพร่ผลงานตีพิมพ์นี้?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success text-white">
                                                    <i class="bi bi-check-lg me-1"></i> อนุมัติ
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" data-type="published" data-id="{{ $pub->id }}" data-title="{{ $pub->pub_name }}">
                                                <i class="bi bi-x-lg me-1"></i> ส่งกลับ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                        ไม่มีผลงานตีพิมพ์ที่ค้างรอการตรวจสอบในขณะนี้
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($publications->hasPages())
                    <div class="p-3 border-top">{{ $publications->links() }}</div>
                @endif

            @elseif($activeTab === 'dips')
                <!-- IP Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-3" style="width: 140px;">ส่งตรวจเมื่อ</th>
                                <th>ชื่อผลงานทรัพย์สินทางปัญญา</th>
                                <th>ประเภท</th>
                                <th>นักวิจัย</th>
                                <th class="text-end pe-3" style="width: 220px;">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dips as $dip)
                                <tr>
                                    <td class="ps-3 text-muted small">
                                        {{ $dip->submitted_at ? \Carbon\Carbon::parse($dip->submitted_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('backend.rdb_dip.show', $dip->dip_id) }}" class="fw-bold text-body text-decoration-none hover-primary">
                                            {{ $dip->dip_data2_name ?? $dip->dip_data2_patent ?? 'ทรัพย์สินทางปัญญา #' . $dip->dip_id }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $dip->dipType->dipt_name ?? '-' }}</span></td>
                                    <td>{{ $dip->researcher?->full_name ?? '-' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="{{ route('backend.rdb_dip.show', $dip->dip_id) }}" class="btn btn-sm btn-outline-secondary" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('backend.workflow.approve', ['type' => 'dip', 'id' => $dip->dip_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติทรัพย์สินทางปัญญานี้?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success text-white">
                                                    <i class="bi bi-check-lg me-1"></i> อนุมัติ
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" data-type="dip" data-id="{{ $dip->dip_id }}" data-title="{{ $dip->dip_data2_name }}">
                                                <i class="bi bi-x-lg me-1"></i> ส่งกลับ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                        ไม่มีทรัพย์สินทางปัญญาที่ค้างรอการตรวจสอบ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($dips->hasPages())
                    <div class="p-3 border-top">{{ $dips->links() }}</div>
                @endif

            @elseif($activeTab === 'utilizes')
                <!-- Utilizes Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-3" style="width: 140px;">ส่งตรวจเมื่อ</th>
                                <th>การนำไปใช้ประโยชน์</th>
                                <th>ด้าน / ประเภท</th>
                                <th>หน่วยงานที่นำไปใช้</th>
                                <th class="text-end pe-3" style="width: 220px;">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($utilizes as $utz)
                                <tr>
                                    <td class="ps-3 text-muted small">
                                        {{ $utz->submitted_at ? \Carbon\Carbon::parse($utz->submitted_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('backend.rdbprojectutilize.show', $utz->utz_id) }}" class="fw-bold text-body text-decoration-none hover-primary">
                                            {{ $utz->project?->pro_nameTH ?? 'การใช้ประโยชน์ #' . $utz->utz_id }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $utz->utilizeType->utz_name ?? '-' }}</span></td>
                                    <td>{{ $utz->utz_department_name ?? '-' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="{{ route('backend.rdbprojectutilize.show', $utz->utz_id) }}" class="btn btn-sm btn-outline-secondary" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('backend.workflow.approve', ['type' => 'utilize', 'id' => $utz->utz_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติการนำไปใช้ประโยชน์นี้?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success text-white">
                                                    <i class="bi bi-check-lg me-1"></i> อนุมัติ
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" data-type="utilize" data-id="{{ $utz->utz_id }}" data-title="การนำไปใช้ประโยชน์">
                                                <i class="bi bi-x-lg me-1"></i> ส่งกลับ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                        ไม่มีการนำไปใช้ประโยชน์ที่ค้างรอการตรวจสอบ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($utilizes->hasPages())
                    <div class="p-3 border-top">{{ $utilizes->links() }}</div>
                @endif
            @elseif($activeTab === 'unlocks')
                <!-- Unlock Requests Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-3" style="width: 140px;">ส่งคำขอเมื่อ</th>
                                <th style="width: 120px;">ประเภท</th>
                                <th>ชื่อผลงาน / เหตุผลที่ขอแก้ไข</th>
                                <th>ผู้ยื่นขอ / สังกัด</th>
                                <th class="text-end pe-3" style="width: 250px;">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $allUnlocks = collect();
                                foreach($unlockProjects as $item) { $allUnlocks->push(['type' => 'project', 'item' => $item, 'id' => $item->pro_id, 'title' => $item->pro_nameTH, 'author' => $item->researchers->first()?->full_name ?? '-', 'dept' => $item->department->department_nameTH ?? '-', 'reason' => $item->unlock_reason, 'date' => $item->unlock_requested_at, 'url' => route('backend.rdb_project.show', $item->pro_id)]); }
                                foreach($unlockPublications as $item) { $allUnlocks->push(['type' => 'published', 'item' => $item, 'id' => $item->pub_id, 'title' => $item->pub_name, 'author' => $item->researcher->full_name ?? '-', 'dept' => $item->department->department_nameTH ?? '-', 'reason' => $item->unlock_reason, 'date' => $item->unlock_requested_at, 'url' => route('backend.rdb_published.show', $item->pub_id)]); }
                                foreach($unlockDips as $item) { $allUnlocks->push(['type' => 'dip', 'item' => $item, 'id' => $item->dip_id, 'title' => $item->dip_data2_name ?? $item->dip_name, 'author' => $item->researcher->full_name ?? '-', 'dept' => '-', 'reason' => $item->unlock_reason, 'date' => $item->unlock_requested_at, 'url' => route('backend.rdb_dip.show', $item->dip_id)]); }
                                foreach($unlockUtilizes as $item) { $allUnlocks->push(['type' => 'utilize', 'item' => $item, 'id' => $item->utz_id, 'title' => 'การนำไปใช้ประโยชน์ #' . $item->pro_id, 'author' => $item->utz_leading ?? '-', 'dept' => $item->utz_department_name ?? '-', 'reason' => $item->unlock_reason, 'date' => $item->unlock_requested_at, 'url' => route('backend.rdbprojectutilize.show', $item->utz_id)]); }
                                $allUnlocks = $allUnlocks->sortByDesc('date');
                            @endphp

                            @forelse($allUnlocks as $u)
                                <tr>
                                    <td class="ps-3 text-muted small">
                                        {{ $u['date'] ? \Carbon\Carbon::parse($u['date'])->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if($u['type'] === 'project')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">โครงการ</span>
                                        @elseif($u['type'] === 'published')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">ตีพิมพ์</span>
                                        @elseif($u['type'] === 'dip')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">ทรัพย์สินฯ</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">ใช้ประโยชน์</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $u['url'] }}" class="fw-bold text-body text-decoration-none hover-primary">
                                            {{ $u['title'] }}
                                        </a>
                                        @if($u['reason'])
                                            <div class="alert alert-warning py-1 px-2 my-1 small border-0 bg-warning-subtle text-dark">
                                                <i class="bi bi-chat-left-quote me-1 text-warning-emphasis"></i><strong>เหตุผลขอแก้ไข:</strong> {{ $u['reason'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="bi bi-person me-1 text-muted"></i>{{ $u['author'] }}</div>
                                        <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $u['dept'] }}</div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="{{ $u['url'] }}" class="btn btn-sm btn-outline-secondary" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <!-- Approve Unlock (Move to Draft) -->
                                            <form action="{{ route('backend.workflow.approve_unlock', ['type' => $u['type'], 'id' => $u['id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติให้แก้ไข และย้ายผลงานนี้ไปยังฉบับร่างหรือไม่?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success text-white" title="อนุมัติให้แก้ไข (ย้ายไปฉบับร่าง)">
                                                    <i class="bi bi-unlock me-1"></i> ย้ายไปฉบับร่าง
                                                </button>
                                            </form>
                                            <!-- Reject Unlock -->
                                            <form action="{{ route('backend.workflow.reject_unlock', ['type' => $u['type'], 'id' => $u['id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันปฏิเสธคำขอแก้ไข คงสถานะเผยแพร่ตามเดิม?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ปฏิเสธคำขอ">
                                                    <i class="bi bi-x-lg me-1"></i> ปฏิเสธ
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                        ไม่มีคำขอเปิดสิทธิ์แก้ไขที่ค้างรอการตรวจสอบ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow border-0">
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-danger text-white py-3">
                    <h6 class="modal-title fw-bold" id="rejectModalLabel">
                        <i class="bi bi-exclamation-octagon me-1"></i> ส่งผลงานกลับไปแก้ไข (Reject with Comments)
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-body-secondary small">รายการผลงาน:</label>
                        <div class="fw-bold text-body" id="modalItemTitle">-</div>
                    </div>
                    <div class="mb-3">
                        <label for="rejectionRemarks" class="form-label fw-bold text-body small">
                            ระบุเหตุผล / สิ่งที่ต้องการให้นักวิจัยแก้ไขเพิ่มเติม <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="rejectionRemarks" name="remarks" rows="4" placeholder="เช่น ไฟล์แนบรายงานวิจัยไม่ครบถ้วน, ข้อมูลบทคัดย่อภาษาอังกฤษยังไม่ได้ระบุ..." required></textarea>
                        <div class="form-text small">ข้อความนี้จะถูกส่งแจ้งเตือนไปยังนักวิจัย และบันทึกในประวัติ Audit Trail</div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3">
                        <i class="bi bi-send me-1"></i> ยืนยันการส่งกลับแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const type = button.getAttribute('data-type');
                const id = button.getAttribute('data-id');
                const title = button.getAttribute('data-title');

                document.getElementById('modalItemTitle').textContent = title || ('ID #' + id);
                document.getElementById('rejectForm').action = '/backend/workflow/' + type + '/' + id + '/reject';
            });
        }
    });
</script>
@endsection
