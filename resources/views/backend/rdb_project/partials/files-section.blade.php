<div class="card">
    <div class="card-header">
        <h5>📁 จัดการไฟล์โครงการ</h5>
    </div>
    <div class="card-body">
        <!-- ✅ Abstract File Upload -->
        <div class="mb-4">
            <h6 class="mb-3">📄 ไฟล์บทคัดย่อ (Abstract)</h6>
            
            @if($project->getAbstractFilePath())
                <div class="alert alert-info mb-3">
                    <strong>ไฟล์ปัจจุบัน:</strong>
                    @if($project->usingNewAbstractPath())
                        <span class="badge badge-success">UUID (ใหม่)</span>
                    @else
                        <span class="badge badge-warning">Legacy (เก่า)</span>
                    @endif
                    <a href="{{ route('backend.rdb_project.view_abstract', $project->pro_id) }}" target="_blank">
                        ดูไฟล์
                    </a>
                </div>
            @endif

            <form action="{{ route('backend.rdb_project.upload_abstract', $project->pro_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" name="pro_abstract_file" class="form-control" accept=".pdf,.doc,.docx">
                    @error('pro_abstract_file')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa fa-upload"></i> อัปโหลดบทคัดย่อ
                </button>
                
                @if($project->getAbstractFilePath())
                    <form action="{{ route('backend.rdb_project.delete_abstract', $project->pro_id) }}" 
                          method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('ต้องการลบไฟล์นี้ใช่หรือไม่?')">
                            <i class="fa fa-trash"></i> ลบ
                        </button>
                    </form>
                @endif
            </form>
        </div>

        <hr>

        <!-- ✅ Report File Upload -->
        <div class="mb-4">
            <h6 class="mb-3">📑 ไฟล์รายงานฉบับสมบูรณ์ (Full Report)</h6>
            
            @if($project->getReportFilePath())
                <div class="alert alert-info mb-3">
                    <strong>ไฟล์ปัจจุบัน:</strong>
                    @if($project->usingNewReportPath())
                        <span class="badge badge-success">UUID (ใหม่)</span>
                    @else
                        <span class="badge badge-warning">Legacy (เก่า)</span>
                    @endif
                    <a href="{{ route('backend.rdb_project.view_report', $project->pro_id) }}" target="_blank">
                        ดูไฟล์
                    </a>
                </div>
            @endif

            <form action="{{ route('backend.rdb_project.upload_report', $project->pro_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" name="pro_file" class="form-control" accept=".pdf,.doc,.docx">
                    @error('pro_file')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="pro_file_show" class="form-check-input" id="proFileShow" value="1">
                    <label class="form-check-label" for="proFileShow">
                        แสดงในหน้าสาธารณะ
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa fa-upload"></i> อัปโหลดรายงาน
                </button>

                @if($project->getReportFilePath())
                    <form action="{{ route('backend.rdb_project.delete_report', $project->pro_id) }}" 
                          method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('ต้องการลบไฟล์นี้ใช่หรือไม่?')">
                            <i class="fa fa-trash"></i> ลบ
                        </button>
                    </form>
                @endif
            </form>
        </div>

        <hr>

        <!-- ✅ Additional Files -->
        <div>
            <h6 class="mb-3">📎 ไฟล์เพิ่มเติม</h6>

            <!-- File List -->
            @if($project->files->count() > 0)
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>ชื่อไฟล์</th>
                            <th>ประเภท</th>
                            <th>ขนาด</th>
                            <th>จำนวนดาวน์โหลด</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->files as $file)
                            <tr>
                                <td>{{ $file->rf_filesname }}</td>
                                <td>
                                    @if($file->usingNewPath())
                                        <span class="badge badge-success">UUID</span>
                                    @else
                                        <span class="badge badge-warning">Legacy</span>
                                    @endif
                                </td>
                                <td>{{ number_format($file->prf_size / 1024, 2) }} KB</td>
                                <td>{{ $file->rf_download ?? 0 }}</td>
                                <td>
                                    <a href="{{ route('backend.rdb_project.file.download', [$project->pro_id, $file->id]) }}" 
                                       class="btn btn-info btn-sm" title="ดาวน์โหลด">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" 
                                            data-target="#editFileModal{{ $file->id }}" title="แก้ไข">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form action="{{ route('backend.rdb_project.file.destroy', [$project->pro_id, $file->id]) }}" 
                                          method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('ต้องการลบไฟล์นี้ใช่หรือไม่?')"
                                                title="ลบ">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- ✅ Edit File Modal -->
                            <div class="modal fade" id="editFileModal{{ $file->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">แก้ไขไฟล์</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('backend.rdb_project.file.update', [$project->pro_id, $file->id]) }}" 
                                              method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>ชื่อไฟล์</label>
                                                    <input type="text" name="rf_filesname" class="form-control" 
                                                           value="{{ $file->rf_filesname }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>ไฟล์ใหม่ (ถ้าต้องการเปลี่ยน)</label>
                                                    <input type="file" name="rf_files" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label>หมายเหตุ</label>
                                                    <textarea name="rf_note" class="form-control" rows="2">{{ $file->rf_note }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-save"></i> บันทึก
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">ไม่มีไฟล์เพิ่มเติม</p>
            @endif

            <!-- ✅ Add New File -->
            <div class="mt-3">
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addFileModal">
                    <i class="fa fa-plus"></i> เพิ่มไฟล์
                </button>
            </div>

            <!-- Add File Modal -->
            <div class="modal fade" id="addFileModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">เพิ่มไฟล์ใหม่</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('backend.rdb_project.file.store', $project->pro_id) }}" 
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>ชื่อไฟล์</label>
                                    <input type="text" name="rf_filesname" class="form-control" required>
                                    @error('rf_filesname')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>อัปโหลดไฟล์</label>
                                    <input type="file" name="rf_files" class="form-control" required>
                                    @error('rf_files')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>หมายเหตุ</label>
                                    <textarea name="rf_note" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="rf_files_show" class="form-check-input" 
                                           id="rfFilesShow" value="1">
                                    <label class="form-check-label" for="rfFilesShow">
                                        แสดงในหน้าสาธารณะ
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> อัปโหลด
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
