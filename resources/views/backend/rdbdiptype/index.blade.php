@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-search-bar :searchRoute="route('backend.rdbdiptype.index')" placeholder="ค้นหาประเภท..." />

    <x-card>
        <x-slot name="actions">
            <a href="{{ route('backend.rdbdiptype.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> เพิ่มข้อมูลใหม่
            </a>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>ชื่อประเภททรัพย์สินทางปัญญา</th>
                        <th class="text-center" style="width: 100px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                    <tr>
                        <td>{{ $items->firstItem() + $index }}</td>
                        <td>{{ $item->dipt_name }}</td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbdiptype.show', $item->dipt_id) }}" class="btn btn-sm btn-outline-primary border-0" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $items->withQueryString()->links() }}
        </div>
    </x-card>
</div>
@endsection
