@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-geo-alt-fill text-danger me-2"></i>จัดการพื้นที่และพิกัด (ต. อ. จ.)</h4>
            <div class="text-secondary small">ข้อมูลตำบล อำเภอ จังหวัด พร้อมพิกัดภูมิศาสตร์ (Latitude / Longitude) สำหรับงานนำไปใช้ประโยชน์ในพื้นที่</div>
        </div>
        <a href="{{ route('backend.rdbchangwat.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มพื้นที่ใหม่
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdbchangwat.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="พิมพ์ชื่อตำบล อำเภอ หรือจังหวัด..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="changwat" class="form-select form-select-sm">
                        <option value="">-- ทุกจังหวัด --</option>
                        @foreach($provinces as $prov)
                            <option value="{{ $prov }}" {{ request('changwat') == $prov ? 'selected' : '' }}>
                                {{ $prov }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    @if(request()->anyFilled(['search', 'changwat']))
                        <a href="{{ route('backend.rdbchangwat.index') }}" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">รหัส</th>
                            <th>ตำบล</th>
                            <th>อำเภอ</th>
                            <th>จังหวัด</th>
                            <th style="width: 200px;">พิกัด (Lat, Long)</th>
                            <th class="text-center" style="width: 140px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><code>#{{ $item->id }}</code></td>
                            <td class="fw-semibold">
                                <a href="{{ route('backend.rdbchangwat.show', $item->id) }}" class="text-decoration-none text-body hover-primary">
                                    {{ $item->tambon_t }}
                                </a>
                                @if($item->tambon_e)
                                    <div class="text-secondary small" style="font-size: 0.75rem;">{{ $item->tambon_e }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $item->amphoe_t }}
                                @if($item->amphoe_e)
                                    <div class="text-secondary small" style="font-size: 0.75rem;">{{ $item->amphoe_e }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                    {{ $item->changwat_t }}
                                </span>
                            </td>
                            <td>
                                @if($item->lat && $item->long)
                                    <a href="https://www.google.com/maps?q={{ $item->lat }},{{ $item->long }}" target="_blank" rel="noopener noreferrer" class="badge bg-light text-dark border text-decoration-none" title="คลิกดูแผนที่">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ round($item->lat, 4) }}, {{ round($item->long, 4) }}
                                    </a>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('backend.rdbchangwat.show', $item->id) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbchangwat.edit', $item->id) }}" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.rdbchangwat.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">ไม่พบข้อมูลพื้นที่</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
                <div class="p-3 border-top">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
