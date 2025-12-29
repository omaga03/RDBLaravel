@push('scripts')
<!-- Flatpickr CDN (Date Picker) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>

<style>
/* Flatpickr Dark Mode tweaks */
[data-bs-theme="dark"] .flatpickr-calendar {
    background: #343a40;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    border: 1px solid #495057;
}
[data-bs-theme="dark"] .flatpickr-calendar.arrowTop:after, 
[data-bs-theme="dark"] .flatpickr-calendar.arrowTop:before {
    border-bottom-color: #343a40;
}
[data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:after, 
[data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:before {
    border-top-color: #343a40;
}
[data-bs-theme="dark"] .flatpickr-day {
    color: #e9ecef;
}
[data-bs-theme="dark"] .flatpickr-day.prevMonthDay, 
[data-bs-theme="dark"] .flatpickr-day.nextMonthDay {
    color: #6c757d;
}
[data-bs-theme="dark"] .flatpickr-day:hover, 
[data-bs-theme="dark"] .flatpickr-day.prevMonthDay:hover, 
[data-bs-theme="dark"] .flatpickr-day.nextMonthDay:hover, 
[data-bs-theme="dark"] .flatpickr-day:focus {
    background: #495057;
    border-color: #495057;
}
[data-bs-theme="dark"] .flatpickr-day.selected, 
[data-bs-theme="dark"] .flatpickr-day.startRange, 
[data-bs-theme="dark"] .flatpickr-day.endRange, 
[data-bs-theme="dark"] .flatpickr-day.selected.inRange, 
[data-bs-theme="dark"] .flatpickr-day.startRange.inRange, 
[data-bs-theme="dark"] .flatpickr-day.endRange.inRange, 
[data-bs-theme="dark"] .flatpickr-day.selected:focus, 
[data-bs-theme="dark"] .flatpickr-day.startRange:focus, 
[data-bs-theme="dark"] .flatpickr-day.endRange:focus, 
[data-bs-theme="dark"] .flatpickr-day.selected:hover, 
[data-bs-theme="dark"] .flatpickr-day.startRange:hover, 
[data-bs-theme="dark"] .flatpickr-day.endRange:hover, 
[data-bs-theme="dark"] .flatpickr-day.selected.prevMonthDay, 
[data-bs-theme="dark"] .flatpickr-day.startRange.prevMonthDay, 
[data-bs-theme="dark"] .flatpickr-day.endRange.prevMonthDay, 
[data-bs-theme="dark"] .flatpickr-day.selected.nextMonthDay, 
[data-bs-theme="dark"] .flatpickr-day.startRange.nextMonthDay, 
[data-bs-theme="dark"] .flatpickr-day.endRange.nextMonthDay {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}
[data-bs-theme="dark"] .flatpickr-months .flatpickr-month {
    background: #343a40;
    color: #fff;
    fill: #fff;
}
[data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month, 
[data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month {
    fill: #fff;
}
[data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month:hover svg, 
[data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month:hover svg {
    fill: #0d6efd;
}
[data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months {
    background: #343a40;
    color: #fff;
}
[data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
    background: #495057;
}
[data-bs-theme="dark"] .flatpickr-current-month input.cur-year {
    color: #fff;
}
[data-bs-theme="dark"] .flatpickr-weekdays {
    background: #343a40;
}
[data-bs-theme="dark"] span.flatpickr-weekday {
    background: #343a40;
    color: #ced4da;
}
</style>

<script>
/**
 * Global Configuration for Flatpickr Thai Buddhist Year
 */
window.flatpickrThaiConfig = {
    dateFormat: "Y-m-d", // Value sent to server
    altInput: true,
    altFormat: "j F Y", // Format for display
    locale: "th",
    disableMobile: true,
    firstDayOfWeek: 0, // Explicitly set Sunday
    formatDate: (date, format, locale) => {
        if (format !== "j F Y") return flatpickr.formatDate(date, format, locale);
        const year = date.getFullYear() + 543;
        const month = locale.months.longhand[date.getMonth()];
        const day = date.getDate();
        return `${day} ${month} ${year}`;
    },
    onReady: function(selectedDates, dateStr, instance) {
        adjustCalendarYear(instance);
    },
    onMonthChange: function(selectedDates, dateStr, instance) {
        setTimeout(() => adjustCalendarYear(instance), 0);
    },
    onYearChange: function(selectedDates, dateStr, instance) {
        setTimeout(() => adjustCalendarYear(instance), 0);
    },
    onOpen: function(selectedDates, dateStr, instance) {
        adjustCalendarYear(instance);
    },
    onValueUpdate: function(selectedDates, dateStr, instance) {
        adjustCalendarYear(instance);
    }
};

function adjustCalendarYear(instance) {
    if (!instance.calendarContainer) return;
    
    const yearElements = instance.calendarContainer.querySelectorAll('.cur-year, .numInput.cur-year');
    const year = parseInt(instance.currentYear);
    
    yearElements.forEach(el => {
        const beYear = year + 543;
        if (el.tagName === 'INPUT') {
            if (el.value != beYear) el.value = beYear;
        } else {
            if (el.textContent != beYear) el.textContent = beYear;
        }
    });
}

/**
 * Helper to initialize Flatpickr with mergeable options
 */
function initThaiFlatpickr(selector, customOptions = {}) {
    if (typeof flatpickr === 'undefined') return;

    if (flatpickr.l10ns && flatpickr.l10ns.th) {
        flatpickr.l10ns.th.firstDayOfWeek = 0; // Sunday start
    }

    // Prepare merged config
    const mergedConfig = { ...window.flatpickrThaiConfig, ...customOptions };
    
    // Ensure vital hooks still call adjustCalendarYear even if customized
    const hooks = ['onReady', 'onMonthChange', 'onYearChange', 'onOpen', 'onValueUpdate', 'onChange'];
    hooks.forEach(hook => {
        const customHook = customOptions[hook];
        mergedConfig[hook] = function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance); 
            if (customHook) customHook(selectedDates, dateStr, instance);
        }
    });

    return flatpickr(selector, mergedConfig);
}

// Global click listener for safety
document.addEventListener('click', function(e) {
    if (e.target.closest('.flatpickr-calendar')) {
        // Find if this calendar belongs to a flatpickr instance
        // Flatpickr instances are often stored on the original element
        document.querySelectorAll('.flatpickr-input').forEach(el => {
            if (el._flatpickr && el._flatpickr.isOpen) {
                adjustCalendarYear(el._flatpickr);
            }
        });
    }
}, true);
</script>
@endpush
