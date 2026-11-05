<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResearchConferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'conference_id' => (string) ($this->con_id ?? ''),
            'title' => (string) ($this->con_name ?? ''),
            'event_date' => (string) ($this->con_even_date ?? ''),
            'submission_deadline' => (string) ($this->con_sub_deadline ?? ''),
            'venue' => (string) ($this->con_venue ?? ''),
            'website' => (string) ($this->con_website ?? ''),
            'view_count' => (int) ($this->con_count ?? 0),
            'image_url' => $this->image_url,
            'detail_html' => (string) ($this->con_detail ?? ''),
            'detail_plain' => (string) ($this->plain_summary ?? ''),
            'public_url' => route('frontend.researchcoferenceinthai.show', $this->id),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
