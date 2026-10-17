@props([
    'title',
    'icon' => 'bi-info-circle',
    'backRoute',
    'editRoute' => null,
    'deleteRoute' => null,
    'canDelete' => true,
    'showPrint' => true
])

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0"><i class="bi {{ $icon }} me-2"></i>{{ $title }}</h2>
    <div class="d-flex align-items-center gap-2 d-print-none flex-wrap">
        <a href="{{ $backRoute }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px; height: 38px;">
            <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
        </a>
        @if($editRoute)
        <a href="{{ $editRoute }}" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 120px; height: 38px;">
            <i class="bi bi-pencil me-2"></i> แก้ไข
        </a>
        @endif
        @if($showPrint)
        <button type="button" onclick="window.print()" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 120px; height: 38px;">
            <i class="bi bi-printer me-2"></i> พิมพ์
        </button>
        @endif
        @if($deleteRoute && $canDelete)
        <form id="delete-form-top" action="{{ $deleteRoute }}" method="POST" class="d-inline-flex m-0 p-0 align-items-center delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 120px; height: 38px;">
                <i class="bi bi-trash me-2"></i> ลบ
            </button>
        </form>
        @endif
        {{ $slot }}
    </div>
</div>
