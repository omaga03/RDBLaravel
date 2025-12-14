@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มข้อมูลโครงการวิจัย</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('backend.rdb_project.store') }}" enctype="multipart/form-data">
                @csrf
                @include('backend.rdb_project._form')
            </form>
        </div>
    </div>
</div>

<!-- CKEditor 4 CDN (Free Version) -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
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
        var isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var uiColor = isDarkMode ? '#343a40' : '#f8f9fa';
        
        // Initialize CKEditor for basic fields (pro_nameTH, pro_nameEN)
        ['pro_nameTH', 'pro_nameEN'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el && !CKEDITOR.instances[id]) {
                var editor = CKEDITOR.replace(id, {
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Subscript', 'Superscript'] }
                    ],
                    height: 80,
                    language: 'th',
                    uiColor: uiColor
                });
                if (isDarkMode) {
                    editor.on('instanceReady', function(e) {
                        e.editor.document.getBody().setStyle('background-color', '#212529');
                        e.editor.document.getBody().setStyle('color', '#dee2e6');
                    });
                }
            }
        });
        
        // Initialize CKEditor for abstract fields
        ['pro_abstract_th', 'pro_abstract_en'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el && !CKEDITOR.instances[id]) {
                var editor = CKEDITOR.replace(id, {
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Subscript', 'Superscript'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
                        { name: 'tools', items: ['Undo', 'Redo'] }
                    ],
                    height: 200,
                    language: 'th',
                    uiColor: uiColor
                });
                if (isDarkMode) {
                    editor.on('instanceReady', function(e) {
                        e.editor.document.getBody().setStyle('background-color', '#212529');
                        e.editor.document.getBody().setStyle('color', '#dee2e6');
                    });
                }
            }
        });
    }
});
</script>

<style>
/* Hide CKEditor security notification */
.cke_notification_warning {
    display: none !important;
}
/* Ensure TomSelect input is visible and cursor is correct */
.ts-control input {
    opacity: 1 !important;
    position: relative !important;
    left: 0 !important;
    min-width: 100px !important;
}
.ts-wrapper.single .ts-control input {
    cursor: text !important;
}

/* TomSelect Dark Mode Support */
[data-bs-theme="dark"] .ts-control {
    background-color: #212529 !important;
    border-color: #495057 !important;
    color: #fff !important;
}
[data-bs-theme="dark"] .ts-dropdown {
    background-color: #343a40 !important;
    border-color: #495057 !important;
    color: #fff !important;
}
[data-bs-theme="dark"] .ts-dropdown .option {
    color: #fff !important;
}
[data-bs-theme="dark"] .ts-dropdown .option:hover,
[data-bs-theme="dark"] .ts-dropdown .active {
    background-color: #0d6efd !important;
    color: #fff !important;
}
[data-bs-theme="dark"] .ts-control .item {
    color: #fff !important;
}
[data-bs-theme="dark"] .ts-wrapper.single .ts-control:after {
    border-color: #fff transparent transparent transparent !important;
}
[data-bs-theme="dark"] .ts-control input {
    color: #e9ecef !important;
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
</style>
@endsection
