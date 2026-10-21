<!-- Flatpickr CDN (Thai Buddhist Calendar) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>

<style>
/* ==========================================================================
   Flatpickr Thai Buddhist Calendar Theme & Dark Mode
   ========================================================================== */
.flatpickr-calendar {
    font-family: 'Prompt', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0,0,0,0.08);
}
.flatpickr-current-month {
    font-size: 1.02rem;
    padding-top: 6px;
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    font-weight: 600;
}
.flatpickr-day.selected, 
.flatpickr-day.startRange, 
.flatpickr-day.endRange {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #fff !important;
}
.flatpickr-day.today {
    border-color: #0d6efd;
}

/* Dark Mode Support */
[data-bs-theme="dark"] .flatpickr-calendar {
    background: #212529;
    box-shadow: 0 12px 30px rgba(0,0,0,0.5);
    border: 1px solid #373b3e;
}
[data-bs-theme="dark"] .flatpickr-calendar.arrowTop:after, 
[data-bs-theme="dark"] .flatpickr-calendar.arrowTop:before {
    border-bottom-color: #212529;
}
[data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:after, 
[data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:before {
    border-top-color: #212529;
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
    background: #343a40;
    border-color: #343a40;
}
[data-bs-theme="dark"] .flatpickr-months .flatpickr-month {
    background: #212529;
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
    background: #212529;
    color: #fff;
}
[data-bs-theme="dark"] .flatpickr-current-month input.cur-year {
    color: #fff;
}
[data-bs-theme="dark"] .flatpickr-weekdays {
    background: #212529;
}
[data-bs-theme="dark"] span.flatpickr-weekday {
    background: #212529;
    color: #adb5bd;
}
</style>

<script>
(function() {
    // 1. Thai Locale Initialization
    if (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.th) {
        flatpickr.l10ns.th.firstDayOfWeek = 0; // Start with Sunday
    }

    const thaiMonthsLong = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    const thaiMonthsShort = [
        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];

    // Helper: Intercept DOM setter on year input elements so that any assignment of CE year (< 2400)
    // is instantly converted to Buddhist Era (พ.ศ.) before rendering in the browser
    function hookYearElement(el) {
        if (!el || el._thaiHooked) return;
        el._thaiHooked = true;

        const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        if (descriptor && descriptor.set) {
            Object.defineProperty(el, 'value', {
                get: function() {
                    return descriptor.get.call(this);
                },
                set: function(val) {
                    let num = parseInt(val, 10);
                    if (!isNaN(num) && num > 1000 && num < 2400) {
                        num += 543;
                        val = num.toString();
                    }
                    descriptor.set.call(this, val);
                },
                configurable: true
            });
        }
    }

    // Helper: Wrap Flatpickr internal instance methods to prevent CE regression during navigation
    function wrapInstanceMethods(instance) {
        if (!instance || instance._thaiMethodsWrapped) return;
        instance._thaiMethodsWrapped = true;

        // 1. Wrap changeMonth
        const origChangeMonth = instance.changeMonth;
        instance.changeMonth = function(value, isOffset) {
            origChangeMonth.call(instance, value, isOffset);
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
            setTimeout(() => adjustCalendarYear(instance), 50);
        };

        // 2. Wrap changeYear
        const origChangeYear = instance.changeYear;
        instance.changeYear = function(newYear) {
            if (newYear >= 2400) newYear -= 543;
            origChangeYear.call(instance, newYear);
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        };

        // 3. Wrap redraw
        const origRedraw = instance.redraw;
        instance.redraw = function() {
            origRedraw.call(instance);
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        };

        // 4. Wrap jumpToDate
        const origJumpToDate = instance.jumpToDate;
        instance.jumpToDate = function(date, triggerChange) {
            origJumpToDate.call(instance, date, triggerChange);
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        };

        // 5. MutationObserver on calendar container
        if (!instance._thaiObserver && window.MutationObserver && instance.calendarContainer) {
            instance._thaiObserver = new MutationObserver(function() {
                adjustCalendarYear(instance);
            });
            instance._thaiObserver.observe(instance.calendarContainer, {
                childList: true,
                subtree: true
            });
        }
    }

    // Helper: Adjust calendar popup header to Buddhist Era (พ.ศ.)
    function adjustCalendarYear(instance) {
        if (!instance || !instance.calendarContainer) return;
        wrapInstanceMethods(instance);

        const year = parseInt(instance.currentYear, 10);
        if (isNaN(year)) return;
        const beYear = year + 543;

        const yearElements = instance.calendarContainer.querySelectorAll('.cur-year, .numInput.cur-year');
        yearElements.forEach(el => {
            hookYearElement(el);
            if (el.tagName === 'INPUT') {
                if (parseInt(el.value, 10) !== beYear) {
                    el.value = beYear;
                }
            } else {
                if (parseInt(el.textContent, 10) !== beYear) {
                    el.textContent = beYear;
                }
            }

            if (!el._thaiBound) {
                el._thaiBound = true;
                ['input', 'change'].forEach(evtName => {
                    el.addEventListener(evtName, function(e) {
                        const val = parseInt(this.value, 10);
                        if (!isNaN(val) && val >= 2400) {
                            e.stopImmediatePropagation();
                            instance.changeYear(val - 543);
                        }
                    }, true);
                });
            }
        });
    }

    // 2. Global Thai Buddhist Flatpickr Config
    window.flatpickrThaiConfig = {
        dateFormat: "Y-m-d", // Value sent to server/database (CE: YYYY-MM-DD)
        altInput: true,      // Display element for user
        altFormat: "j F Y",  // Default display: "21 ตุลาคม 2569"
        locale: "th",
        disableMobile: true, // Consistent custom picker across mobile and desktop
        firstDayOfWeek: 0,
        formatDate: function(date, format, locale) {
            if (!date) return "";
            const beYear = date.getFullYear() + 543;
            const day = date.getDate();
            const monthIdx = date.getMonth();

            if (format === "d/m/Y" || format === "d-m-Y") {
                const dd = String(day).padStart(2, '0');
                const mm = String(monthIdx + 1).padStart(2, '0');
                return `${dd}/${mm}/${beYear}`;
            }
            if (format === "j M Y" || format === "d M Y") {
                return `${day} ${thaiMonthsShort[monthIdx]} ${beYear}`;
            }
            // Default full Thai format: e.g. "21 ตุลาคม 2569"
            return `${day} ${thaiMonthsLong[monthIdx]} ${beYear}`;
        },
        parseDate: function(dateStr, format) {
            if (!dateStr) return undefined;
            if (dateStr instanceof Date) return dateStr;
            const str = String(dateStr).trim();

            // Match ISO YYYY-MM-DD
            const iso = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
            if (iso) {
                let y = parseInt(iso[1], 10);
                if (y >= 2400) y -= 543;
                return new Date(y, parseInt(iso[2], 10) - 1, parseInt(iso[3], 10));
            }

            // Match d/m/Y or d-m-Y
            const dmy = str.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
            if (dmy) {
                let y = parseInt(dmy[3], 10);
                if (y >= 2400) y -= 543;
                return new Date(y, parseInt(dmy[2], 10) - 1, parseInt(dmy[1], 10));
            }

            // Match Thai text month
            for (let i = 0; i < 12; i++) {
                if (str.includes(thaiMonthsLong[i]) || str.includes(thaiMonthsShort[i])) {
                    const clean = str.replace(thaiMonthsLong[i], ' ').replace(thaiMonthsShort[i], ' ').trim();
                    const parts = clean.split(/\s+/);
                    if (parts.length >= 2) {
                        const d = parseInt(parts[0], 10);
                        let y = parseInt(parts[1], 10);
                        if (y >= 2400) y -= 543;
                        return new Date(y, i, d);
                    }
                }
            }

            try {
                return flatpickr.parseDate(str, format);
            } catch(e) {
                return new Date(str);
            }
        },
        onReady: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        },
        onMonthChange: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
            setTimeout(() => adjustCalendarYear(instance), 50);
        },
        onYearChange: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        },
        onOpen: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        },
        onValueUpdate: function(selectedDates, dateStr, instance) {
            adjustCalendarYear(instance);
            setTimeout(() => adjustCalendarYear(instance), 0);
        }
    };

    // 3. Helper to initialize Flatpickr with merged options
    window.initThaiFlatpickr = function(selector, customOptions = {}) {
        if (typeof flatpickr === 'undefined') return null;

        const elements = (typeof selector === 'string')
            ? document.querySelectorAll(selector)
            : (selector instanceof NodeList || Array.isArray(selector) ? selector : [selector]);

        const instances = [];
        elements.forEach(el => {
            if (!el) return;
            // Return existing instance if already initialized without custom override
            if (el._flatpickr && Object.keys(customOptions).length === 0) {
                instances.push(el._flatpickr);
                return;
            }

            // Convert native type="date" to type="text"
            if (el.tagName === 'INPUT' && el.type === 'date') {
                el.type = 'text';
            }

            const mergedConfig = { ...window.flatpickrThaiConfig, ...customOptions };

            // Ensure vital hooks still adjust year
            const hooks = ['onReady', 'onMonthChange', 'onYearChange', 'onOpen', 'onValueUpdate', 'onChange', 'onClose'];
            hooks.forEach(hook => {
                const customHook = customOptions[hook];
                mergedConfig[hook] = function(selectedDates, dateStr, instance) {
                    adjustCalendarYear(instance);
                    setTimeout(() => adjustCalendarYear(instance), 0);
                    if (hook === 'onMonthChange') {
                        setTimeout(() => adjustCalendarYear(instance), 50);
                    }
                    if (customHook) customHook(selectedDates, dateStr, instance);
                };
            });

            const fp = flatpickr(el, mergedConfig);
            wrapInstanceMethods(fp);
            instances.push(fp);
        });

        return (typeof selector === 'string' && elements.length === 1) ? instances[0] : (instances.length === 1 ? instances[0] : instances);
    };

    // 4. Auto-initialize on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        const targets = document.querySelectorAll('.datepicker:not(.flatpickr-input), .flatpickr:not(.flatpickr-input), .flatpickr-thai:not(.flatpickr-input)');
        targets.forEach(el => {
            if (!el._flatpickr) {
                window.initThaiFlatpickr(el);
            }
        });
    });

    // 5. Global click listener for calendar popup safety
    document.addEventListener('click', function(e) {
        if (e.target.closest('.flatpickr-calendar')) {
            document.querySelectorAll('.flatpickr-input').forEach(el => {
                if (el._flatpickr && el._flatpickr.isOpen) {
                    adjustCalendarYear(el._flatpickr);
                }
            });
        }
    }, true);
})();
</script>
