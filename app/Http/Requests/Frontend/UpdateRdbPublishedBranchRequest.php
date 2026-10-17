<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbPublishedBranchRequest extends FormRequest
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
        // The primary key for RdbPublishedBranch is 'branch_id'
        // Assuming the route parameter is 'id' and represents the branch_id
        $branch_id = $this->route('id');

        return [
            'branch_name' => [
                'required',
                'string',
                'max:255',
                // Add unique rule if needed
                // Rule::unique('rdb_published_branch', 'branch_name')->ignore($branch_id, 'branch_id'),
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
            'branch_name.required' => 'กรุณาระบุชื่อสาขา',
            'branch_name.string' => 'ชื่อสาขาต้องเป็นตัวอักษร',
            'branch_name.max' => 'ชื่อสาขาต้องไม่เกิน 255 ตัวอักษร',
        ];
    }
}
