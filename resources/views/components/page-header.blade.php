@props([
    'title',
    'icon' => 'bi-info-circle',
    'backRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'canDelete' => true,
    'showPrint' => true,
    'breadcrumbs' => []
])

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        @if(!empty($breadcrumbs))
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                @foreach($breadcrumbs as $label => $url)
                    @if($loop->last || empty($url))
                        <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
        @endif
        <h3 class="mb-0 fw-semibold text-body page-title"><i class="bi {{ $icon }} me-2 text-primary"></i>{{ $title }}</h3>
    </div>
    <div class="d-flex align-items-center gap-2 d-print-none flex-wrap">
        @if($backRoute)
        <a href="{{ $backRoute }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center shadow-sm" style="min-width: 105px; height: 38px; font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1.5"></i> ย้อนกลับ
        </a>
        @endif
        @if($editRoute)
        <a href="{{ $editRoute }}" class="btn btn-warning text-dark d-inline-flex justify-content-center align-items-center shadow-sm" style="min-width: 105px; height: 38px; font-size: 0.85rem;">
            <i class="bi bi-pencil me-1.5"></i> แก้ไข
        </a>
        @endif
        @if($showPrint)
        <button type="button" onclick="window.print()" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 105px; height: 38px; font-size: 0.85rem;">
            <i class="bi bi-printer me-1.5"></i> พิมพ์
        </button>
        @endif
        @if($deleteRoute && $canDelete)
        <form id="delete-form-top" action="{{ $deleteRoute }}" method="POST" class="d-inline-flex m-0 p-0 align-items-center delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 105px; height: 38px; font-size: 0.85rem;">
                <i class="bi bi-trash me-1.5"></i> ลบ
            </button>
        </form>
        @endif
        {{ $slot }}
    </div>
</div>
