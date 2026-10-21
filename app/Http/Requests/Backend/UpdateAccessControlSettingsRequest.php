<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessControlSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'download_policy_full_report' => 'required|in:public,members_only,admin_only',
            'download_policy_abstract' => 'required|in:public,members_only',
            'download_policy_attachments' => 'required|in:public,members_only,admin_only',
            'download_require_login_message' => 'required|string|max:1000',
            'google_allowed_domains' => 'nullable|string|max:255',
            'google_client_id' => 'nullable|string|max:500',
            'google_client_secret' => 'nullable|string|max:500',
        ];
    }
}
