<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbDipRequest extends FormRequest
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
     * @return array<string, \Illuminate->Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $dip_id = $this->route('id');

        return [
            'dip_type' => 'nullable|integer',
            'dipt_id' => 'nullable|integer|exists:rdb_dip_type,dipt_id',
            'pro_id' => 'nullable|integer|exists:rdb_project,pro_id',
            'researcher_id' => 'nullable|integer|exists:rdb_researcher,researcher_id',
            'dip_request_number' => 'nullable|string|max:255',
            'dip_request_date' => 'nullable|date',
            'dip_request_dateget' => 'nullable|date',
            'dip_number' => 'nullable|string|max:255',
            'dip_publication_date' => 'nullable|date',
            'dip_publication_no' => 'nullable|string|max:255',
            'dip_patent_number' => 'nullable|string|max:255',
            'dip_startdate' => 'nullable|date',
            'dip_enddate' => 'nullable|date',
            'dip_note' => 'nullable|string',
            'data_show' => 'nullable|integer',
            // File validations
            'dip_files' => 'nullable|file|max:10240',
            'dip_data1_files' => 'nullable|file|max:10240',
            'dip_data2_files_con' => 'nullable|file|max:10240',
            'dip_data3_filesass1' => 'nullable|file|max:10240',
            'dip_data3_filesass2' => 'nullable|file|max:10240',
            'dip_data3_filesass3' => 'nullable|file|max:10240',
            'dip_data3_drawing_picture' => 'nullable|file|max:10240',
            'dip_data_forms_request' => 'nullable|file|max:10240',
        ];
    }
}
