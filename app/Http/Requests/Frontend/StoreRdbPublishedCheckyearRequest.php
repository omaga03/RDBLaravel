<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbPublishedCheckyearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller's Gate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'year_id' => 'nullable|integer|exists:rdb_year,year_id',
            'rdbyearedu_start' => 'nullable|integer',
            'rdbyearedu_end' => 'nullable|integer',
            'rdbyearbud_start' => 'nullable|integer',
            'rdbyearbud_end' => 'nullable|integer',
            // 'user_created', 'user_updated', 'created_at', 'updated_at' are handled by controller
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'year_id.exists' => 'ปีงบประมาณไม่ถูกต้อง',
            'rdbyearedu_start.integer' => 'ปีการศึกษาเริ่มต้นต้องเป็นตัวเลข',
            'rdbyearedu_end.integer' => 'ปีการศึกษาสิ้นสุดต้องเป็นตัวเลข',
            'rdbyearbud_start.integer' => 'ปีงบประมาณเริ่มต้นต้องเป็นตัวเลข',
            'rdbyearbud_end.integer' => 'ปีงบประมาณสิ้นสุดต้องเป็นตัวเลข',
        ];
    }
}
