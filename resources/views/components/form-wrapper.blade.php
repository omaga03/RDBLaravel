@props([
    'title',
    'icon' => null,
    'mode' => 'create', // 'create' or 'edit'
    'actionRoute' => null,
    'action' => null,
    'method' => 'POST',
    'backRoute' => null,
    'enctype' => null,
    'colClass' => 'col-12'
])

@php
    $actionUrl = $actionRoute ?? $action ?? '#';
    $back = $backRoute ?? url()->previous();
    $headerColor = $mode === 'create' ? 'success' : 'primary';
    $submitText = $mode === 'create' ? 'บันทึกข้อมูล' : 'บันทึกการแก้ไข';
    $headerIcon = $icon ?? ($mode === 'create' ? 'bi-plus-circle' : 'bi-pencil-square');
@endphp

<div class="row">
    <div class="{{ $colClass }}">
        <div class="card shadow-sm border">
            <div class="card-header bg-{{ $headerColor }} text-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi {{ $headerIcon }} me-2"></i>{{ $title }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ $actionUrl }}" method="POST" {{ $enctype ? "enctype=$enctype" : '' }}>
                    @csrf
                    @if(strtoupper($method) !== 'POST')
                        @method($method)
                    @endif
                    
                    {{ $slot }}
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ $back }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center px-4" style="min-width: 120px;">
                            <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                        </a>
                        <button type="submit" class="btn btn-{{ $headerColor }} d-inline-flex justify-content-center align-items-center px-4" style="min-width: 150px;">
                            <i class="bi bi-save me-2"></i> {{ $submitText }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
