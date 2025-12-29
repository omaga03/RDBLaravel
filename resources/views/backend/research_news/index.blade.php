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
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;">รูปภาพ</th>
                            <th>หัวข้อข่าว/กิจกรรม</th>
                            <th style="width: 100px;" class="text-center">เข้าชม</th>
                            <th style="width: 80px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                @if($item->news_img)
                                    <img src="{{ asset('storage/uploads/news/' . $item->news_img) }}" 
                                         alt="รูปข่าว" class="rounded" 
                                         style="width: 60px; height: 45px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 45px;">
                                        <i class="bi bi-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">
                                    {{ $item->news_name }}
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                                    @if($item->news_type)
                                        <span class="ms-2"><i class="bi bi-tag me-1"></i>{{ $item->news_type }}</span>
                                    @endif
                                </small>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-eye me-1"></i>{{ number_format($item->news_count ?? 0) }}
                                </span>
                            </td>
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
