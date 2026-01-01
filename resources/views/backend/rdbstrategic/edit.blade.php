@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขยุทธศาสตร์"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbstrategic.update', $item->strategic_id)"
    method="PUT"
    :backRoute="route('backend.rdbstrategic.show', $item->strategic_id)"
>
    <div class="row g-3">
        <div class="col-md-8">
            <label for="strategic_nameTH" class="form-label">
                ชื่อยุทธศาสตร์ <span class="text-danger">*</span>
            </label>
            <input type="text" name="strategic_nameTH" id="strategic_nameTH" 
                   class="form-control @error('strategic_nameTH') is-invalid @enderror" 
                   value="{{ old('strategic_nameTH', $item->strategic_nameTH) }}" required maxlength="255">
            @error('strategic_nameTH')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-4">
            <label for="year_id" class="form-label">ปีงบประมาณ</label>
            <x-tom-select 
                name="year_id" 
                :options="$years->pluck('year_name', 'year_id')->toArray()"
                :value="old('year_id', $item->year_id)"
                placeholder="เลือกปี..."
            />
        </div>
    </div>
</x-form-wrapper>
@endsection