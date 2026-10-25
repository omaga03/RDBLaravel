<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectUtilizeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('utz_date') && $this->utz_date !== null && $this->utz_date !== '') {
            $this->merge([
                'utz_date' => \App\Helpers\ThaiDateHelper::toIsoDate($this->utz_date),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pro_id' => 'required|exists:rdb_project,pro_id', // pro_id can also be updated
            'utz_department_name' => 'required|string|max:255',
            'utz_date' => 'required|date',
            'chw_id' => 'required|exists:rdb_changwat,id',
            'utz_files_upload' => 'nullable|file|mimes:pdf|max:51200', // Max 50MB

            // Other fields from RdbProjectUtilize model
            'utz_year_id' => 'nullable|integer',
            'utz_year_bud' => 'nullable|integer',
            'utz_year_edu' => 'nullable|integer',
            'utz_leading' => 'nullable|string|max:255',
            'utz_leading_position' => 'nullable|string|max:255',
            'utz_department_address' => 'nullable|string',
            'utz_group' => 'nullable|array', // Multi-select array
            'utz_group.*' => 'nullable|integer', // Ensure each item in array is integer
            'utz_group_qa' => 'nullable|boolean',
            'utz_detail' => 'nullable|string',
            'utz_budget' => 'nullable|numeric',
            'utz_count' => 'nullable|integer',
            'utz_countfile' => 'nullable|integer',
        ];
    }
}
