@props([
    'title' => null,
    'icon' => null, 
    'color' => 'primary', // primary, success, info, warning, danger, blue
    'actions' => null
])

@php
    $headerClass = match($color) {
        'blue' => 'bg-gradient-blue text-white',
        'success' => 'bg-success text-white',
        'primary' => 'bg-primary text-white', 
        'info' => 'bg-info text-white',
        'warning' => 'bg-warning text-dark',
        'danger' => 'bg-danger text-white',
        default => 'bg-light text-dark'
    };
    
    // Gradient Blue Custom Style
    if($color === 'blue') {
        $style = 'background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%) !important; color: #fff; border-color: #1a237e;';
    } else {
        $style = '';
    }
@endphp

<div {{ $attributes->merge(['class' => 'card shadow-sm mb-4']) }}>
    @if($title)
    <div class="card-header {{ $color !== 'blue' ? $headerClass : '' }}" style="{{ $style }}">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $title }}
            </h5>
            @if($actions)
            <div>
                {{ $actions }}
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endif
</div>
