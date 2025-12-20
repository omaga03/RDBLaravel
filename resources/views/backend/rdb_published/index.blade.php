@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-journal-text"></i> จัดการข้อมูลการตีพิมพ์ (Publications)</h1>
         <a href="{{ route('backend.rdb_published.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มข้อมูลตีพิมพ์
        </a>
    </div>

    @include('backend.rdb_published.partials.search_form')

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead>
                        <tr>
                            <th>ชื่อผลงาน</th>
                            <th>ประเภท</th>
                            <th>คณะผู้เขียน</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">
                                    <i class="bi bi-file-earmark-text me-1 text-secondary"></i> {{ $item->pub_name }}
                                </div>
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-calendar-event me-1"></i> {{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}
                                </div>
                                <small class="text-secondary d-block">
                                    <i class="bi bi-journal-bookmark me-1"></i> {{ $item->pub_name_journal }}
                                </small>
                            </td>
                            <td>
                                <i class="bi bi-tag me-1 text-secondary"></i> {{ $item->pubtype->pubtype_subgroup ?? '-' }}
                            </td>
                            <td>
                                @if($item->authors->isNotEmpty())
                                    @php
                                        // Prioritize main author if needed, or just take first
                                        $firstAuthor = $item->authors->first();
                                        $remainingCount = $item->authors->count() - 1;
                                    @endphp
                                    <div>
                                        <i class="bi bi-person-circle text-secondary"></i>
                                        {{ $firstAuthor->researcher_fname }} {{ $firstAuthor->researcher_lname }}
                                        @if(isset($authorTypes[$firstAuthor->pivot->pubta_id]))
                                            <small class="text-muted">({{ $authorTypes[$firstAuthor->pivot->pubta_id] }})</small>
                                        @endif
                                        @if($remainingCount > 0)
                                            <span class="text-muted small">(+{{ $remainingCount }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{-- Actions --}}
                                <a href="{{ route('backend.rdb_published.show', $item->getKey()) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
