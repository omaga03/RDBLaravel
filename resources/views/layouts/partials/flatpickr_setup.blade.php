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
    formatDate: (date, format, locale) => {
        // Return standard format if not the display format
        if (format !== "j F Y") {
                return flatpickr.formatDate(date, format, locale);
        }
        
        // For display (altInput), convert Year to Buddhist Era
        const buddhistYear = date.getFullYear() + 543;
        return flatpickr.formatDate(date, "j F", locale) + " " + buddhistYear;
    },
    onMonthChange: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            if (instance.config.onMonthChangeOriginal) instance.config.onMonthChangeOriginal(selectedDates, dateStr, instance);
    },
    onYearChange: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            if (instance.config.onYearChangeOriginal) instance.config.onYearChangeOriginal(selectedDates, dateStr, instance);

    },
    onOpen: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            if (instance.config.onOpenOriginal) instance.config.onOpenOriginal(selectedDates, dateStr, instance);
    }
};

function adjustCalendarYear(instance) {
    setTimeout(() => {
        const yearInput = instance.currentYearElement;
        if (yearInput) {
                // Get the ACTUAL Gregorian year from the instance
                const currentYear = instance.currentYear;
                const buddhistYear = currentYear + 543;
                
                // Visually update the input value
                if (yearInput.value != buddhistYear) {
                yearInput.value = buddhistYear;
                }
        }
    }, 0);
}

/**
 * Helper to initialize Flatpickr with mergeable options
 */
function initThaiFlatpickr(selector, customOptions = {}) {
    if (typeof flatpickr === 'undefined') return;

    if (flatpickr.l10ns && flatpickr.l10ns.th) {
        flatpickr.l10ns.th.firstDayOfWeek = 0; // Sunday start
    }

    // Merge callbacks carefully
    const mergedConfig = { ...window.flatpickrThaiConfig, ...customOptions };
    
    // If customOptions has callbacks that are also in base config, wrap them
    // Note: The base config already calls "Original" hooks if they exist in config.
    // But since we are merging, customOptions will overwrite base attributes.
    // So we need to ensure the base logic (adjustCalendarYear) runs.
    
    // Instead of complex merging, let's just ensure we call our helper in the merged hooks
    const hooks = ['onMonthChange', 'onYearChange', 'onOpen'];
    hooks.forEach(hook => {
        const customHook = customOptions[hook];
        mergedConfig[hook] = function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance); // Always run our hack
            if (customHook) customHook(selectedDates, dateStr, instance); // Run custom logic
        }
    });

    return flatpickr(selector, mergedConfig);
}
</script>
@endpush
