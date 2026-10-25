@props(['searchRoute', 'exportRoute' => null, 'collapsed' => true, 'simplePlaceholder' => 'พิมพ์คำค้นหา...'])
@php
    $action = $searchRoute ?? '#';
@endphp

<div class="card search-box mb-4 shadow-sm">
    <div class="card-header search-header d-flex justify-content-between align-items-center"  
         data-bs-toggle="collapse" 
         data-bs-target="#searchCollapse" 
         aria-expanded="{{ $collapsed ? 'false' : 'true' }}" 
         aria-controls="searchCollapse">
        
        <h5 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="bi bi-search me-2 opacity-75"></i> ค้นหาข้อมูล (Search)
        </h5>
        
        <div class="d-flex align-items-center gap-2">
            @if($exportRoute)
                <a href="{{ $exportRoute . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}" 
                   class="btn btn-sm btn-success text-white shadow-sm" 
                   title="ส่งออกข้อมูลเป็น Excel (CSV)" 
                   onclick="event.stopPropagation();">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> ส่งออก Excel
                </a>
            @endif
            @if(request()->except(['page']))
                <a href="{{ $action }}" class="btn btn-sm btn-warning shadow-sm" title="ล้างค่าการค้นหา" onclick="event.stopPropagation();">
                    <i class="bi bi-arrow-counterclockwise"></i> ล้างตัวกรอง
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-toggle-search d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" aria-label="Toggle search panel">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
    </div>
    
    <div class="collapse {{ $collapsed ? '' : 'show' }}" id="searchCollapse">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="GET">
                
                {{-- Tabs for switching modes --}}
                <ul class="nav nav-tabs mb-3 border-bottom" id="searchTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-3 py-2 border-0 fw-semibold d-flex align-items-center" id="simple-tab" data-bs-toggle="tab" data-bs-target="#simple-search" type="button" role="tab" aria-controls="simple-search" aria-selected="true">
                            <i class="bi bi-search me-2"></i> ค้นหาทั่วไป
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-2 border-0 fw-semibold d-flex align-items-center" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced-search" type="button" role="tab" aria-controls="advanced-search" aria-selected="false">
                            <i class="bi bi-sliders me-2"></i> ค้นหาละเอียด
                        </button>
                    </li>
                </ul>
                <style>
                    #searchTabs .nav-link {
                        background: transparent !important;
                        border-radius: 0;
                        border-bottom: 2px solid transparent !important;
                        color: var(--bs-secondary-color) !important;
                        margin-bottom: -1px;
                        transition: color 0.15s ease, border-color 0.15s ease;
                    }
                    #searchTabs .nav-link.active {
                        border-bottom: 2px solid var(--rdb-secondary) !important;
                        background: transparent !important;
                        color: var(--rdb-secondary) !important;
                    }
                    #searchTabs .nav-link:hover {
                        color: var(--bs-body-color) !important;
                    }
                    [data-bs-theme="dark"] #searchTabs .nav-link.active {
                        border-bottom: 2px solid #60A5FA !important;
                        color: #60A5FA !important;
                    }
                </style>
                
                <div class="tab-content" id="searchTabContent">
                    {{-- Simple Search --}}
                    <div class="tab-pane fade show active" id="simple-search" role="tabpanel" aria-labelledby="simple-tab">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ $simplePlaceholder }}" value="{{ request('search') }}">
                            @if(request()->filled('search'))
                                <a href="{{ $action }}" class="btn btn-outline-secondary" title="ล้างคำค้นหา">
                                    <i class="bi bi-x-circle me-1"></i> ล้างค่า
                                </a>
                            @endif
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
