<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $branchId = $this->route('id'); // Assumes route parameter is named 'id'

        return [
            'branch_name' => [
                'required',
                'max:255',
                Rule::unique('rdb_published_branch', 'branch_name')->ignore($branchId, 'branch_id'),
            ],
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
            'branch_name.unique' => 'ชื่อสาขาการวิจัยนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
