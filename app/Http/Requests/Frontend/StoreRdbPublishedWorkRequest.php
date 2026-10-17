<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbPublishedWorkRequest extends FormRequest
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
            'published_id' => 'required|integer|exists:rdb_published,id',
            'researcher_id' => 'nullable|integer|exists:rdb_researcher,researcher_id',
            'pubta_id' => 'nullable|integer|exists:rdb_published_type_author,pubta_id',
            'pubw_main' => 'nullable|string|max:1',
            'pubw_bud' => 'nullable|numeric',
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
            'published_id.required' => 'กรุณาระบุงานวิจัยที่ตีพิมพ์',
            'published_id.exists' => 'งานวิจัยที่ตีพิมพ์ไม่ถูกต้อง',
            'researcher_id.exists' => 'นักวิจัยไม่ถูกต้อง',
            'pubta_id.exists' => 'ประเภทผู้แต่งไม่ถูกต้อง',
        ];
    }
}
