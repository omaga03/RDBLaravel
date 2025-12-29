<!-- CKEditor 4 CDN (Free Version) -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<!-- CKEditor Custom Styles & Dark Mode Support -->
<style>
/* Hide CKEditor security notification */
.cke_notification_warning {
    display: none !important;
}

[data-bs-theme="dark"] .cke {
    border-color: #495057 !important;
}
[data-bs-theme="dark"] .cke_top,
[data-bs-theme="dark"] .cke_bottom {
    background: #343a40 !important;
    border-color: #495057 !important;
}
[data-bs-theme="dark"] .cke_toolgroup {
    background: #495057 !important;
    border-color: #6c757d !important;
}
[data-bs-theme="dark"] .cke_button,
[data-bs-theme="dark"] .cke_combo_button {
    background: transparent !important;
}
[data-bs-theme="dark"] .cke_button_icon {
    filter: invert(1);
}
[data-bs-theme="dark"] .cke_button_label {
    color: #fff !important;
}
[data-bs-theme="dark"] .cke_wysiwyg_frame,
[data-bs-theme="dark"] .cke_wysiwyg_div {
    background: #212529 !important;
}
[data-bs-theme="dark"] textarea.cke_source {
    background-color: #212529 !important;
    color: #fff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if CKEDITOR is available
    var checkCKEditor = setInterval(function() {
        if (typeof CKEDITOR !== 'undefined') {
            clearInterval(checkCKEditor);
            initCKEditors();
        }
    }, 100);
    
    function initCKEditors() {
        // Pre-define dark mode styles in CKEditor's internal stylesheet
        // This ensures styles are available immediately when class is applied, preventing FOUC
        CKEDITOR.addCss(
            'body.dark-mode { background-color: #212529 !important; color: #dee2e6 !important; }' +
            'body.dark-mode::-webkit-scrollbar { width: 12px; height: 12px; }' +
            'body.dark-mode::-webkit-scrollbar-track { background: #212529; }' +
            'body.dark-mode::-webkit-scrollbar-thumb { background-color: #495057; border-radius: 10px; border: 3px solid #212529; }' +
            'body.dark-mode::-webkit-scrollbar-corner { background: #212529; }'
        );

        // Global Defaults: Prevent auto-paragraphing (<p>) and use <br> for line breaks
        CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
        CKEDITOR.config.autoParagraph = false;
        CKEDITOR.config.fillEmptyBlocks = false; // Prevent &nbsp; filler
        CKEDITOR.config.ignoreEmptyParagraph = true;

        var isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var uiColor = isDarkMode ? '#343a40' : '#f8f9fa';
        // Set initial bodyClass based on current theme
        var initialBodyClass = isDarkMode ? 'dark-mode' : '';
        
        // Helper to toggle class on active editor body
        function updateEditorTheme(editor, isDark) {
            if (editor.document && editor.document.getBody()) {
                if (isDark) {
                    editor.document.getBody().addClass('dark-mode');
                } else {
                    editor.document.getBody().removeClass('dark-mode');
                }
            }
            // Update config so that if mode is switched (re-creating iframe), the correct class is used
            editor.config.bodyClass = isDark ? 'dark-mode' : '';
        }

        // Initialize CKEditor for basic fields (class: ckeditor-basic)
        var basicFields = document.querySelectorAll('.ckeditor-basic');
        basicFields.forEach(function(el) {
            if (el.id && !CKEDITOR.instances[el.id]) {
                var editor = CKEDITOR.replace(el.id, {
                    toolbar: [
                        { name: 'basicstyles', items: ['Source', '-', 'Bold', 'Italic', 'Subscript', 'Superscript'] }
                    ],
                    height: 80,
                    removeButtons: '',
                    language: 'th',
                    uiColor: uiColor,
                    enterMode: CKEDITOR.ENTER_BR,
                    autoParagraph: false,
                    bodyClass: initialBodyClass // Apply class at creation time
                });
                
                // Fallback/Safety: Ensure class is correct on ready (e.g. if config didn't catch it)
                editor.on('contentDom', function() {
                    var currentThemeDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    updateEditorTheme(this, currentThemeDark);
                });
            }
        });
        
        // Initialize CKEditor for standard fields (class: ckeditor-standard)
        var standardFields = document.querySelectorAll('.ckeditor-standard');
        standardFields.forEach(function(el) {
            if (el.id && !CKEDITOR.instances[el.id]) {
                var editor = CKEDITOR.replace(el.id, {
                    toolbar: [
                        { name: 'basicstyles', items: ['Source', '-', 'Bold', 'Italic', 'Subscript', 'Superscript'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
                        { name: 'tools', items: ['Undo', 'Redo'] }
                    ],
                    height: 200,
                    language: 'th',
                    uiColor: uiColor,
                    enterMode: CKEDITOR.ENTER_BR,
                    autoParagraph: false,
                    bodyClass: initialBodyClass // Apply class at creation time
                });
                
                // Fallback/Safety
                editor.on('contentDom', function() {
                    var currentThemeDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    updateEditorTheme(this, currentThemeDark);
                });
            }
        });

        // Initialize CKEditor for full fields with Link support (class: ckeditor-full)
        var fullFields = document.querySelectorAll('.ckeditor-full');
        fullFields.forEach(function(el) {
            if (el.id && !CKEDITOR.instances[el.id]) {
                var editor = CKEDITOR.replace(el.id, {
                    toolbar: [
                        { name: 'document', items: ['Source'] },
                        { name: 'clipboard', items: ['Undo', 'Redo'] },
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Table', 'HorizontalRule', 'SpecialChar'] },
                        { name: 'styles', items: ['Format', 'FontSize'] },
                        { name: 'colors', items: ['TextColor', 'BGColor'] }
                    ],
                    height: 300,
                    language: 'th',
                    uiColor: uiColor,
                    enterMode: CKEDITOR.ENTER_BR,
                    autoParagraph: false,
                    bodyClass: initialBodyClass
                });
                
                editor.on('contentDom', function() {
                    var currentThemeDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    updateEditorTheme(this, currentThemeDark);
                });
            }
        });

        // Observer for real-time theme changes
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-bs-theme') {
                    var isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    
                    for (var instanceName in CKEDITOR.instances) {
                        var editor = CKEDITOR.instances[instanceName];
                        // Update live editor and its config for future rebuilds
                        updateEditorTheme(editor, isDarkMode);
                    }
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });
    }
});
</script>
