<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreDipRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dip_request_number' => 'required|max:255',
            'dipt_id' => 'required|exists:rdb_dip_type,dipt_id', // Added exists validation
            'dip_data2_name' => 'required|string',
            'researcher_id' => 'required|exists:rdb_researcher,researcher_id', // Added exists validation
            // Add other fields from the form as nullable or with appropriate rules
            'dip_type' => 'nullable|integer',
            'dip_files' => 'nullable|file|mimes:pdf|max:51200', // Allow optional file upload
            'dip_startdate' => 'nullable|date',
            'dip_enddate' => 'nullable|date',
            'pro_id' => 'nullable|exists:rdb_project,pro_id',
            'dip_request_date' => 'nullable|date',
            'dip_request_dateget' => 'nullable|date',
            'dip_number' => 'nullable|string|max:255',
            'dip_publication_date' => 'nullable|date',
            'dip_publication_no' => 'nullable|string|max:255',
            'dip_patent_number' => 'nullable|string|max:255',
            'dip_data1_datestart' => 'nullable|date',
            'dip_data1_files' => 'nullable|file|max:51200',
            'dip_data2_patent' => 'nullable|string|max:255',
            'dip_data2_name' => 'nullable|string', // Already required above
            'dip_data2_agent' => 'nullable|string|max:255',
            'dip_data2_status' => 'nullable|string|max:255',
            'dip_data2_dateend' => 'nullable|date',
            'dip_data2_conclusion' => 'nullable|string',
            'dip_data2_files_con' => 'nullable|file|max:51200',
            'dip_data2_assertion' => 'nullable|string',
            'dip_data2_tag' => 'nullable|string',
            'dip_data3_allassertion' => 'nullable|string',
            'dip_data3_filesass1' => 'nullable|file|max:51200',
            'dip_data3_filesass2' => 'nullable|file|max:51200',
            'dip_data3_filesass3' => 'nullable|file|max:51200',
            'dip_data3_drawing_picture' => 'nullable|file|max:51200',
            'dip_data_forms_request' => 'nullable|file|max:51200',
            'dip_url' => 'nullable|url|max:255',
            'dip_note' => 'nullable|string',
        ];
    }
}
