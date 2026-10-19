@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-bell me-2 text-primary"></i>ศูนย์การแจ้งเตือน (Notifications)</h4>
            <p class="text-muted small mb-0">รายการแจ้งเตือนสถานะผลงานวิจัยและการดำเนินงานที่เกี่ยวข้องกับคุณ</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read_all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check2-all me-1"></i> อ่านทั้งหมดแล้ว
                    </button>
                </form>
            @endif
            <a href="{{ route('backend.site.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับแดชบอร์ด
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary">
                ประวัติการแจ้งเตือน ({{ $notifications->total() }} รายการ)
            </span>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="badge bg-danger rounded-pill">
                    ยังไม่ได้อ่าน {{ auth()->user()->unreadNotifications->count() }} รายการ
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $item)
                        @php
                            $isUnread = is_null($item->read_at);
                            $data = $item->data;
                            $title = $data['title'] ?? 'การแจ้งเตือนระบบ';
                            $message = $data['message'] ?? '';
                            $url = $data['url'] ?? null;
                            $actionText = $data['action_text'] ?? 'ดูรายละเอียด';
                            
                            // Badge and icon styling
                            $icon = 'bi-bell';
                            $colorClass = 'text-primary';
                            if (str_contains(strtolower($title), 'อนุมัติ') || str_contains(strtolower($message), 'อนุมัติ')) {
                                $icon = 'bi-check-circle-fill';
                                $colorClass = 'text-success';
                            } elseif (str_contains(strtolower($title), 'ส่งกลับ') || str_contains(strtolower($title), 'ปฏิเสธ') || str_contains(strtolower($message), 'ส่งกลับ')) {
                                $icon = 'bi-exclamation-triangle-fill';
                                $colorClass = 'text-warning';
                            } elseif (str_contains(strtolower($title), 'ส่งตรวจสอบ')) {
                                $icon = 'bi-send-fill';
                                $colorClass = 'text-info';
                            }
                        @endphp
                        <div class="list-group-item p-3 {{ $isUnread ? 'bg-light bg-opacity-75' : '' }} border-start-0 border-end-0">
                            <div class="d-flex align-items-start gap-3">
                                <div class="fs-4 {{ $colorClass }} mt-1">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                                        <h6 class="mb-0 fw-bold {{ $isUnread ? 'text-primary' : 'text-body' }}">
                                            {{ $title }}
                                            @if($isUnread)
                                                <span class="badge bg-danger ms-2 font-monospace" style="font-size: 0.65rem;">NEW</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted ms-2 text-nowrap">
                                            <i class="bi bi-clock me-1"></i>{{ $item->created_at->diffForHumans() }}
                                            ({{ $item->created_at->format('d/m/Y H:i') }})
                                        </small>
                                    </div>
                                    <p class="mb-2 text-secondary small">{{ $message }}</p>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($isUnread)
                                            <form action="{{ route('notifications.read', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.8rem;">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> {{ $actionText }} & ทำเครื่องหมายว่าอ่านแล้ว
                                                </button>
                                            </form>
                                        @elseif($url)
                                            <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.8rem;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> {{ $actionText }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
                    <h6 class="fw-semibold">ไม่มีรายการแจ้งเตือน</h6>
                    <p class="small mb-0">เมื่อมีสถานะหรือกิจกรรมเกี่ยวกับผลงานวิจัยของคุณ ระบบจะแจ้งเตือนให้ทราบที่นี่</p>
                </div>
            @endif
        </div>
        @if($notifications->hasPages())
            <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
