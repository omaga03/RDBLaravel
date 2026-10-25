<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbResearcherEducationRequest extends FormRequest
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
        // The primary key for RdbResearcherEducation is 'reedu_id'
        // Assuming the route parameter is 'id' and represents the reedu_id
        $reedu_id = $this->route('id');

        return [
            'researcher_id' => 'nullable|integer|exists:rdb_researcher,researcher_id',
            'reedu_status' => 'nullable|string|max:50',
            'reedu_year' => 'nullable|integer',
            'reedu_type' => 'nullable|string|max:100',
            'reedu_name' => 'required|string|max:255',
            'reedu_department' => 'nullable|string|max:255',
            'reedu_major' => 'nullable|string|max:255',
            'reedu_cational' => 'nullable|string|max:255',
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
            'reedu_name.required' => 'กรุณาระบุชื่อการศึกษา',
            'researcher_id.exists' => 'นักวิจัยไม่ถูกต้อง',
        ];
    }
}
