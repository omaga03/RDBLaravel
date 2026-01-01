@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person-badge me-2"></i>จัดการคำนำหน้าชื่อ</h2>
        <a href="{{ route('backend.rdbprefix.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มคำนำหน้า
        </a>
    </div>

    {{-- Search Box --}}
    <x-search-bar :searchRoute="route('backend.rdbprefix.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">คำนำหน้า (ภาษาไทย)</label>
                <input type="text" name="prefix_nameTH" class="form-control" value="{{ request('prefix_nameTH') }}" placeholder="ค้นหาคำนำหน้า...">
            </div>
            <div class="col-md-6">
                <label class="form-label">ตัวย่อ</label>
                <input type="text" name="prefix_abbreviationTH" class="form-control" value="{{ request('prefix_abbreviationTH') }}" placeholder="ค้นหาตัวย่อ...">
            </div>
        </div>
    </x-search-bar>

    {{-- Data Table --}}
    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">รหัส</th>
                        <th>คำนำหน้า (ไทย)</th>
                        <th>ตัวย่อ</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->prefix_id }}</code></td>
                        <td class="fw-bold">{{ $item->prefix_nameTH ?? '-' }}</td>
                        <td>{{ $item->prefix_abbreviationTH ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbprefix.show', $item->prefix_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($items->hasPages())
        <div class="mt-3">
            {{ $items->withQueryString()->links() }}
        </div>
        @endif
    </x-card>
</div>
@endsection