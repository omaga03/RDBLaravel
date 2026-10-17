<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbDateeventTypeRequest extends FormRequest
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
            'evt_name' => 'required|string|max:255|unique:rdb_dateevent_type,evt_name',
            'evt_color' => 'nullable|string|max:20',
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
            'evt_name.unique' => 'ชื่อประเภทกิจกรรมนี้มีอยู่ในระบบแล้ว',
            'evt_name.required' => 'กรุณาระบุชื่อประเภทกิจกรรม',
        ];
    }
}
