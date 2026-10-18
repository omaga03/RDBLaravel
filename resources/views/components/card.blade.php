@props([
    'title' => null,
    'icon' => null, 
    'color' => 'primary', // primary, success, info, warning, danger, blue
    'actions' => null
])

@php
    $headerClass = match($color) {
        'blue' => 'text-white',
        'primary' => 'text-white',
        'success' => 'text-white', 
        'info' => 'bg-info text-white',
        'warning' => 'text-white',
        'accent' => 'text-white',
        'danger' => 'bg-danger text-white',
        default => 'bg-body-tertiary text-body'
    };
    
    // UI/UX Pro Max Gradients
    if($color === 'blue' || $color === 'primary') {
        $style = 'background: var(--rdb-gradient-primary) !important; color: #fff; border-color: #0F2744;';
    } elseif($color === 'success') {
        $style = 'background: var(--rdb-gradient-emerald) !important; color: #fff; border-color: #059669;';
    } elseif($color === 'warning' || $color === 'accent') {
        $style = 'background: var(--rdb-gradient-gold) !important; color: #fff; border-color: #D97706;';
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
