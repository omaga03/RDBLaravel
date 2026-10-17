@extends('layouts.frontend')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>{{ $project->pro_nameTH }}</h3>
                    @if($project->pro_nameEN)
                        <p class="text-muted">{{ $project->pro_nameEN }}</p>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>รายละเอียด:</strong>
                        <p>{{ $project->pro_detail }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>วันเริ่มต้น:</strong> {{ $project->pro_start }}<br>
                            <strong>วันสิ้นสุด:</strong> {{ $project->pro_end }}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>งบประมาณ:</strong> {{ number_format($project->pro_budget, 2) }}<br>
                            <strong>สถานะ:</strong> {{ $project->projectStatus->ps_nameTH ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ Files Section - Read Only -->
            @include('frontend.rdb_project.partials.files-section')
        </div>

        <div class="col-md-4">
            <!-- ... existing sidebar ... -->
        </div>
    </div>
</div>
@endsection
