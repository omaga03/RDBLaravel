<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectGroupRequest extends FormRequest
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
            'pro_id' => 'required|integer|exists:rdb_project,pro_id',
            'pgroup_id' => 'required|integer|exists:rdb_groupproject,pgroup_id',
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
            'pro_id.required' => 'กรุณาระบุโครงการ',
            'pro_id.exists' => 'โครงการไม่ถูกต้อง',
            'pgroup_id.required' => 'กรุณาระบุกลุ่มโครงการ',
            'pgroup_id.exists' => 'กลุ่มโครงการไม่ถูกต้อง',
        ];
    }
}
