@props([
    'name',
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => 'เลือก...',
    'remoteUrl' => null,
    'multiple' => false,
    'maxItems' => null,
    'create' => false,
    'minSearchLength' => 0, // 0 = load on focus, >0 = require typing
    'maxOptions' => 10,
    'required' => false,
    'class' => 'form-select'
])

@php
    $id = $id ?? $name;
    $uniqueId = str_replace(['-', '.', '[', ']'], '_', $id);
    
    // Normalize options to consistent format
    $normalizedOptions = [];
    foreach($options as $key => $label) {
        if(is_array($label)) {
            $normalizedOptions[] = $label;
        } else {
            $normalizedOptions[] = ['value' => $key, 'text' => $label];
        }
    }
@endphp

<div wire:ignore>
    <select 
        name="{{ $name }}{{ $multiple ? '[]' : '' }}" 
        id="{{ $id }}"
        class="{{ $class }}"
        @if($multiple) multiple @endif
        @if($required) required @endif
        autocomplete="off"
        {{ $attributes }}
    >
        @if(!$multiple && !$remoteUrl)
        <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($normalizedOptions as $opt)
            <option value="{{ $opt['value'] }}" @selected((is_array($value) ? in_array($opt['value'], $value) : $opt['value'] == $value))>
                {{ $opt['text'] }}
            </option>
        @endforeach
    </select>
</div>

@push('scripts')
<script>
(function() {
    function initTomSelect_{{ $uniqueId }}() {
        const el = document.getElementById('{{ $id }}');
        if(!el || el.tomselect) return;

        const settings = {
            create: {{ $create ? 'true' : 'false' }},
            maxItems: {{ $maxItems ?? ($multiple ? 'null' : '1') }},
            maxOptions: {{ $maxOptions }},
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '{{ $placeholder }}',
            plugins: ['clear_button'],
            highlight: true,
            render: {
                option: function(data, escape) {
                    return '<div>' + (data._highlight || escape(data.text)) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            }
        };

        @if($remoteUrl)
        settings.load = function(query, callback) {
            @if($minSearchLength > 0)
            if (query.length < {{ $minSearchLength }}) return callback();
            @endif
            
            fetch('{{ $remoteUrl }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(json => {
                    callback(json.items || json.results || json);
                }).catch(() => {
                    callback();
                });
        };
        @if($minSearchLength > 0)
        settings.preload = false;
        settings.loadThrottle = 300;
        @else
        settings.preload = 'focus';
        @endif
        @endif

        new TomSelect(el, settings);
    }

    // Initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTomSelect_{{ $uniqueId }});
    } else {
        initTomSelect_{{ $uniqueId }}();
    }
})();
</script>
@endpush
