@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-newspaper"></i> จัดการข่าว/กิจกรรม (News & Activities)</h1>
        <a href="{{ route('backend.research_news.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มข่าวใหม่
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead>
                        <tr>
                            <th>หัวข้อข่าว/กิจกรรม</th>
                            <th>ประเภท</th>
                            <th>วันที่ลงข่าว</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $item->news_name }}</div>
                            </td>
                            <td>
                                @if($item->news_type == 1)
                                    <span class="badge bg-info text-dark">ข่าวประชาสัมพันธ์</span>
                                @elseif($item->news_type == 2)
                                    <span class="badge bg-success">การประชุม/อบรม</span>
                                @else
                                    <span class="badge bg-secondary">อื่นๆ</span>
                                @endif
                            </td>
                            <td>{{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}</td>
                            <td>
                                <a href="{{ route('backend.research_news.show', $item->id) }}" class="btn btn-sm btn-info text-white" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
