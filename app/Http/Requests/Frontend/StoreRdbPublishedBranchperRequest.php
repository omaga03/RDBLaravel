<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbPublishedBranchperRequest extends FormRequest
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
            'branch_id' => 'nullable|integer|exists:rdb_published_branch,branch_id',
            'branchper_percent' => 'nullable|numeric',
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
            'branch_id.exists' => 'สาขาการวิจัยไม่ถูกต้อง',
            'branchper_percent.numeric' => 'เปอร์เซ็นต์ต้องเป็นตัวเลข',
        ];
    }
}
