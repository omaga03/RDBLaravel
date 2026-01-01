@props([
    'title',
    'icon' => 'bi-pencil-square',
    'mode' => 'create', // 'create' or 'edit'
    'actionRoute',
    'method' => 'POST',
    'backRoute',
    'enctype' => null
])

@php
    $action = $actionRoute ?? '#';
    $headerColor = $mode === 'create' ? 'success' : 'primary';
    $submitText = $mode === 'create' ? 'บันทึกข้อมูล' : 'บันทึกการแก้ไข';
    $headerIcon = $mode === 'create' ? 'bi-plus-circle' : 'bi-pencil-square';
@endphp

<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-{{ $headerColor }} text-white">
                    <h5 class="mb-0">
                        <i class="bi {{ $icon ?? $headerIcon }} me-2"></i>{{ $title }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ $action }}" method="POST" {{ $enctype ? "enctype=$enctype" : '' }}>
                        @csrf
                        @if($method !== 'POST')
                            @method($method)
                        @endif
                        
                        {{ $slot }}
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ $backRoute }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                                <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                            </a>
                            <button type="submit" class="btn btn-{{ $headerColor }} d-inline-flex justify-content-center align-items-center" style="min-width: 150px;">
                                <i class="bi bi-save me-2"></i> {{ $submitText }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
