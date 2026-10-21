<div class="card">
    <div class="card-header">
        <h5>📁 ไฟล์โครงการ</h5>
    </div>
    <div class="card-body">
        <!-- ✅ Abstract File - Read Only -->
        @if($project->getAbstractFilePath())
            <div class="mb-3">
                <h6>📄 บทคัดย่อ (Abstract)</h6>
                <a href="{{ route('frontend.rdb_project.view_abstract', $project->pro_id) }}" 
                   class="btn btn-info btn-sm" target="_blank">
                    <i class="fa fa-file-pdf"></i> ดูบทคัดย่อ
                </a>
                <small class="text-muted d-block mt-1">
                    ดูแล้ว: {{ $project->pro_count_abs ?? 0 }} ครั้ง
                </small>
            </div>
            <hr>
        @endif

        <!-- ✅ Report File - Read Only (แสดงเฉพาะ pro_file_show = 0 เปิด) -->
        @if($project->getReportFilePath() && (int)$project->pro_file_show === 0)
            <div class="mb-3">
                <h6>📑 รายงานฉบับสมบูรณ์ (Full Report)</h6>
                <a href="{{ route('frontend.rdb_project.view_report', $project->pro_id) }}" 
                   class="btn btn-info btn-sm" target="_blank">
                    <i class="fa fa-file-pdf"></i> ดูรายงาน
                </a>
                <small class="text-muted d-block mt-1">
                    ดูแล้ว: {{ $project->pro_count_full ?? 0 }} ครั้ง
                </small>
            </div>
            <hr>
        @elseif($project->getReportFilePath() && (int)$project->pro_file_show === 1)
            <div class="mb-3">
                <h6>📑 รายงานฉบับสมบูรณ์ (Full Report)</h6>
                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="fa fa-lock"></i> ปกปิดเล่มรายงาน (ไม่เผยแพร่)</span>
            </div>
            <hr>
        @elseif($project->getReportFilePath() && (int)$project->pro_file_show === 2)
            <div class="mb-3">
                <h6>📑 รายงานฉบับสมบูรณ์ (Full Report)</h6>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="fa fa-pencil"></i> ฉบับร่าง (ยังไม่เผยแพร่)</span>
            </div>
            <hr>
        @endif

        <!-- ✅ Additional Files - Read Only (แสดงเฉพาะ rf_files_show = 1) -->
        @php
            $publicFiles = $project->projectFiles()
                ->where('rf_files_show', 1)
                ->orderBy('id', 'desc')
                ->get();
        @endphp

        @if($publicFiles->count() > 0)
            <div>
                <h6>📎 ไฟล์เพิ่มเติม</h6>
                <div class="list-group">
                    @foreach($publicFiles as $file)
                        <a href="{{ route('frontend.rdb_project.download_file', [$project->pro_id, $file->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fa fa-file"></i> {{ $file->rf_filesname }}
                                </h6>
                                <small class="text-muted">
                                    {{ number_format($file->prf_size / 1024, 2) }} KB
                                </small>
                            </div>
                            @if($file->rf_note)
                                <p class="mb-1 small text-muted">{{ $file->rf_note }}</p>
                            @endif
                            <small class="text-secondary">
                                ดาวน์โหลดแล้ว: {{ $file->rf_download ?? 0 }} ครั้ง
                            </small>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- ✅ No files message -->
        @if(!$project->getAbstractFilePath() && 
            (!$project->getReportFilePath() || (int)$project->pro_file_show !== 0) && 
            $publicFiles->count() == 0)
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> ไม่มีไฟล์ที่เปิดให้สาธารณะ
            </div>
        @endif
    </div>
</div>
