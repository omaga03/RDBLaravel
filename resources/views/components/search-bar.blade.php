@props(['searchRoute', 'collapsed' => true, 'simplePlaceholder' => 'พิมพ์คำค้นหา...'])
@php
    $action = $searchRoute ?? '#';
@endphp

<div class="card search-box mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center"  
         data-bs-toggle="collapse" 
         data-bs-target="#searchCollapse" 
         aria-expanded="{{ $collapsed ? 'false' : 'true' }}" 
         aria-controls="searchCollapse"
         style="cursor: pointer; background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%); color: #fff;">
        
        <h5 class="mb-0"><i class="bi bi-search me-2"></i> ค้นหาข้อมูล (Search)</h5>
        
        <div class="d-flex align-items-center gap-2">
            @if(request()->except(['page']))
                <a href="{{ $action }}" class="btn btn-sm btn-warning" title="ล้างค่าการค้นหา" onclick="event.stopPropagation();">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-light">
                <i class="bi bi-chevron-{{ $collapsed ? 'down' : 'up' }}"></i>
            </button>
        </div>
    </div>
    
    <div class="collapse {{ $collapsed ? '' : 'show' }}" id="searchCollapse">
        <div class="card-body">
            <form action="{{ $action }}" method="GET">
                
                {{-- Tabs for switching modes --}}
                <ul class="nav nav-tabs mb-4 border-bottom-0" id="searchTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-2 border-0 fw-bold d-flex align-items-center" id="simple-tab" data-bs-toggle="tab" data-bs-target="#simple-search" type="button" role="tab" aria-controls="simple-search" aria-selected="true" style="transition: all 0.3s ease;">
                            <i class="bi bi-search me-2"></i> ค้นหาทั่วไป
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-2 border-0 fw-bold d-flex align-items-center" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced-search" type="button" role="tab" aria-controls="advanced-search" aria-selected="false" style="transition: all 0.3s ease;">
                            <i class="bi bi-sliders me-2"></i> ค้นหาละเอียด
                        </button>
                    </li>
                </ul>
                <style>
                    #searchTabs .nav-link {
                        background: transparent !important;
                        border-radius: 0;
                        border-bottom: 2px solid transparent !important;
                        color: #6c757d !important; /* Standard secondary gray */
                    }
                    #searchTabs .nav-link.active {
                        border-bottom: 2px solid #1a237e !important;
                        background: transparent !important;
                        color: #1a237e !important;
                    }
                    #searchTabs .nav-link:hover {
                        border-bottom: 2px solid #3949ab !important;
                        color: #3949ab !important;
                    }
                    /* Dark Mode Support */
                    [data-bs-theme="dark"] #searchTabs .nav-link.active {
                        border-bottom: 2px solid #60a5fa !important;
                        color: #60a5fa !important;
                    }
                    [data-bs-theme="dark"] #searchTabs .nav-link:not(.active) {
                        color: #adb5bd !important;
                    }
                </style>
                
                <div class="tab-content" id="searchTabContent">
                    {{-- Simple Search --}}
                    <div class="tab-pane fade show active" id="simple-search" role="tabpanel" aria-labelledby="simple-tab">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ $simplePlaceholder }}" value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
                        </div>
                    </div>
                    
                    {{-- Advanced Search --}}
                    <div class="tab-pane fade" id="advanced-search" role="tabpanel" aria-labelledby="advanced-tab">
                        {{ $slot }}
                        
                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <a href="{{ $action }}" class="btn btn-secondary me-2"><i class="bi bi-arrow-counterclockwise"></i> ล้างค่า</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
    </div>
</div>
