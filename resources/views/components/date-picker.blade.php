@props([
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => 'เลือกวันที่...',
    'required' => false
])

@php
    $id = $id ?? $name;
@endphp

<div class="input-group">
    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
    <input type="text" 
           name="{{ $name }}" 
           id="{{ $id }}" 
           value="{{ $value }}" 
           class="form-control flatpickr-thai" 
           placeholder="{{ $placeholder }}"
           @if($required) required @endif
           {{ $attributes }}
    >
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initThaiFlatpickr('#{{ $id }}');
    });
</script>
@endpush
