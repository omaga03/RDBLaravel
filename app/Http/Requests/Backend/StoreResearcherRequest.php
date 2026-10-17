<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreResearcherRequest extends FormRequest
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
            'researcher_fname' => 'required|string|max:255',
            'researcher_lname' => 'required|string|max:255',
            'researcher_email' => 'nullable|email|unique:rdb_researcher,researcher_email',
            'fileimg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // Increased max size to 50MB
            
            // Other fields from RdbResearcher model
            'citizen_id' => 'required|string|max:13', // Required for new researcher
            'researcher_codeid' => 'nullable|string|max:32',
            'tea_code' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'maj_id' => 'nullable|exists:rdb_dep_major,maj_id',
            'researcher_gender' => 'nullable|string|in:M,F',
            'prefix_id' => 'nullable|exists:rdb_prefix,prefix_id',
            'researcher_fnameEN' => 'nullable|string|max:255',
            'researcher_lnameEN' => 'nullable|string|max:255',
            'restatus_id' => 'nullable|exists:rdb_researcher_status,restatus_id',
            'researcher_birthdate' => 'nullable|date',
            'researcher_address' => 'nullable|string',
            'researcher_workaddress' => 'nullable|string',
            'researcher_tel' => 'nullable|string|max:20',
            'researcher_mobile' => 'nullable|string|max:20',
            'researcher_fax' => 'nullable|string|max:20',
            'scopus_authorId' => 'nullable|string|max:255',
            'researcher_hindex' => 'nullable|integer',
            'scopus_synced_at' => 'nullable|date',
            'orcid' => 'nullable|string|max:255',
            'researcher_note' => 'nullable|string',
        ];
    }
}
