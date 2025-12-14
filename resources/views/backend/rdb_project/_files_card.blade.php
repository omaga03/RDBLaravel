<div class="card shadow-sm mb-4">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-down"></i> ไฟล์เอกสาร (Files)</h5>
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#projectFilesModal">
            <i class="bi bi-gear"></i> จัดการข้อมูล (Manage)
        </button>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            @if($project->pro_abstract_file)
                <a href="{{ route('backend.rdb_project.view_abstract', $project->pro_id) }}" target="_blank" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-file-earmark-pdf fs-4 me-2"></i> 
                        📄 บทคัดย่อ<br><small class="text-muted">Abstract File</small>
                    </span>
                    <span class="badge bg-primary rounded-pill" title="จำนวนดาวน์โหลด">
                        <i class="bi bi-eye"></i> {{ $project->pro_count_abs ?? 0 }}
                    </span>
                </a>
            @else
                <button class="btn btn-light text-start text-muted" disabled>
                    <i class="bi bi-file-earmark-x fs-4 me-2"></i> ไม่มีไฟล์บทคัดย่อ
                </button>
            @endif

            @if($project->pro_file)
                <a href="{{ route('backend.rdb_project.view_report', $project->pro_id) }}" target="_blank" class="btn btn-outline-success text-start d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-file-earmark-pdf fs-4 me-2"></i>
                        📚 รายงานฉบับสมบูรณ์<br><small class="text-muted">Full Report</small>
                    </span>
                    <span class="badge bg-success rounded-pill" title="จำนวนดาวน์โหลด">
                        <i class="bi bi-eye"></i> {{ $project->pro_count_full ?? 0 }}
                    </span>
                </a>
            @else
                <button class="btn btn-light text-start text-muted" disabled>
                    <i class="bi bi-file-earmark-x fs-4 me-2"></i> ไม่มีไฟล์รายงานฉบับสมบูรณ์
                </button>
            @endif
        </div>
    </div>
</div>
