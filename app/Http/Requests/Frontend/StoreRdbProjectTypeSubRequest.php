<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectTypeSubRequest extends FormRequest
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
            'pt_id' => 'nullable|integer|exists:rdb_project_types,pt_id',
            'pts_name' => 'required|string|max:255',
            // 'user_created', 'user_updated' are handled by controller
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
            'pt_id.exists' => 'ประเภทโครงการไม่ถูกต้อง',
            'pts_name.required' => 'กรุณาระบุชื่อประเภทโครงการย่อย',
            'pts_name.string' => 'ชื่อประเภทโครงการย่อยต้องเป็นตัวอักษร',
            'pts_name.max' => 'ชื่อประเภทโครงการย่อยต้องไม่เกิน 255 ตัวอักษร',
        ];
    }
}
