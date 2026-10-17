<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbPublishedBranchperRequest extends FormRequest
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
        // The primary key for RdbPublishedBranchper is 'branchper_id'
        // Assuming the route parameter is 'id' and represents the branchper_id
        $branchper_id = $this->route('id');

        return [
            'year_id' => 'nullable|integer|exists:rdb_year,year_id',
            'branch_id' => 'nullable|integer|exists:rdb_published_branch,branch_id',
            'branchper_percent' => 'nullable|numeric',
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
            'branch_id.exists' => 'สาขาการวิจัยไม่ถูกต้อง',
            'branchper_percent.numeric' => 'เปอร์เซ็นต์ต้องเป็นตัวเลข',
        ];
    }
}
