@props([
    'name' => 'pro_id',
    'id' => 'pro_id',
    'selected' => null, // Selected Project ID
    'initialText' => '', // Pre-calculated rich text for selected item
    'label' => 'โครงการวิจัย',
    'required' => false,
    'placeholder' => '-- พิมพ์เพื่อค้นหาโครงการ --'
])

<div class="mb-3">
    @if($label)
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    @endif
    
    <select class="form-select project-search-select" id="{{ $id }}" name="{{ $name }}" @if($required) required @endif>
        <option value="">{{ $placeholder }}</option>
        @if($selected && $initialText)
            <option value="{{ $selected }}" selected>{{ $initialText }}</option>
        @endif
    </select>
</div>

@once
    @push('scripts')
        <!-- TomSelect CDN (Load once) -->
        @if(!defined('TOMSELECT_LOADED'))
            @php define('TOMSELECT_LOADED', true); @endphp
            <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        @endif

    @endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('{{ $id }}');
    if (el) {
        new TomSelect(el, {
            create: false,
            openOnFocus: true,
            persist: false,
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: '{{ $placeholder }}',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                // Use Centralized Search Route
                fetch('{{ route("backend.rdb_project.search") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => {
                        // Controller returns { results: [...] }
                        callback(json.results || json); 
                    }).catch(() => {
                        callback();
                    });
            },
            render: {
                option: function(data, escape) {
                    let html = '<div>' + (data._highlight || escape(data.text));
                    if (data.utilization_rows && data.utilization_rows.length > 0) {
                        data.utilization_rows.forEach(function(row) {
                             html += '<div class="text-muted small mt-1" style="font-size: 0.85em; margin-left:1em;">' +
                                     '<i class="bi bi-arrow-return-right"></i> ' + escape(row) + 
                                     '</div>';
                        });
                    }
                    html += '</div>';
                    return html;
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                no_results: function(data, escape) {
                    return '<div class="no-results p-2 text-muted">ไม่พบข้อมูล</div>';
                }
            }
        });
    }
});
</script>
@endpush
