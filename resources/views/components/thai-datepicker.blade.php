@props([
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => 'เลือกวันที่...',
    'required' => false,
    'min' => null, // For date validation (must be after this date)
    'max' => null, // For date validation (must be before this date)
    'linkedTo' => null, // ID of linked date picker (for start-end validation)
    'linkType' => null, // 'start' or 'end' - this field is start/end of range
    'class' => 'form-control'
])

@php
    $id = $id ?? $name;
    $uniqueId = str_replace(['-', '.', '[', ']'], '_', $id);
@endphp

<input 
    type="text"
    name="{{ $name }}"
    id="{{ $id }}"
    value="{{ $value }}"
    placeholder="{{ $placeholder }}"
    class="{{ $class }}"
    @if($required) required @endif
    autocomplete="off"
    {{ $attributes }}
>

@push('scripts')
<script>
(function() {
    function initThaiDatePicker_{{ $uniqueId }}() {
        const el = document.getElementById('{{ $id }}');
        if(!el || el._flatpickr) return;

        const thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const thaiMonthsFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        // Convert display format (Thai) to storage format (Y-m-d)
        function toThaiDate(date) {
            if(!date) return '';
            const d = new Date(date);
            const day = d.getDate();
            const month = thaiMonths[d.getMonth()];
            const year = d.getFullYear() + 543;
            return day + ' ' + month + ' ' + year;
        }

        // Parse Thai date string to Date object
        function parseThaiDate(str) {
            if(!str) return null;
            const parts = str.split(' ');
            if(parts.length < 3) return null;
            const day = parseInt(parts[0]);
            const monthIdx = thaiMonths.indexOf(parts[1]);
            const thaiMonthFullIdx = thaiMonthsFull.findIndex(m => parts[1].includes(m.substring(0, 3)));
            const month = monthIdx >= 0 ? monthIdx : (thaiMonthFullIdx >= 0 ? thaiMonthFullIdx : 0);
            const year = parseInt(parts[2]) - 543;
            return new Date(year, month, day);
        }

        const config = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j M Y',
            locale: 'th',
            allowInput: true,
            @if($min)
            minDate: '{{ $min }}',
            @endif
            @if($max)
            maxDate: '{{ $max }}',
            @endif
            formatDate: function(date, format) {
                if(format === 'j M Y') {
                    return toThaiDate(date);
                }
                // Default ISO format for storage
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + d;
            },
            parseDate: function(dateStr, format) {
                // Try Thai format first
                const thaiParsed = parseThaiDate(dateStr);
                if(thaiParsed) return thaiParsed;
                // Fallback to ISO
                return new Date(dateStr);
            },
            @if($linkedTo && $linkType === 'end')
            onChange: function(selectedDates, dateStr, instance) {
                const startPicker = document.getElementById('{{ $linkedTo }}');
                if(startPicker && startPicker._flatpickr && selectedDates[0]) {
                    const startDate = startPicker._flatpickr.selectedDates[0];
                    if(startDate && selectedDates[0] < startDate) {
                        alert('วันสิ้นสุดต้องไม่น้อยกว่าวันเริ่มต้น');
                        instance.clear();
                    }
                }
            }
            @endif
            @if($linkedTo && $linkType === 'start')
            onChange: function(selectedDates, dateStr, instance) {
                const endPicker = document.getElementById('{{ $linkedTo }}');
                if(endPicker && endPicker._flatpickr && selectedDates[0]) {
                    endPicker._flatpickr.set('minDate', selectedDates[0]);
                }
            }
            @endif
        };

        flatpickr(el, config);
    }

    // Load Flatpickr if not loaded
    if(typeof flatpickr === 'undefined') {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        script.onload = function() {
            // Load Thai locale
            const thLocale = document.createElement('script');
            thLocale.src = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js';
            thLocale.onload = initThaiDatePicker_{{ $uniqueId }};
            document.head.appendChild(thLocale);
        };
        document.head.appendChild(script);
    } else {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initThaiDatePicker_{{ $uniqueId }});
        } else {
            initThaiDatePicker_{{ $uniqueId }}();
        }
    }
})();
</script>
@endpush
