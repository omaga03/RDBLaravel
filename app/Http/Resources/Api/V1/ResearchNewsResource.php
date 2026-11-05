<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResearchNewsResource extends JsonResource
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
            'news_id' => (string) ($this->news_id ?? ''),
            'title' => (string) ($this->news_name ?? ''),
            'type' => (string) ($this->news_type ?? ''),
            'date' => $this->news_date ? (string) $this->news_date : null,
            'event_start' => $this->news_event_start ? (string) $this->news_event_start : null,
            'event_end' => $this->news_event_end ? (string) $this->news_event_end : null,
            'event_guarantee' => $this->news_event_guarantee ? (string) $this->news_event_guarantee : null,
            'reference' => (string) ($this->news_reference ?? ''),
            'source_link' => (string) ($this->news_link ?? ''),
            'view_count' => (int) ($this->news_count ?? 0),
            'image_url' => $this->image_url,
            'detail_html' => (string) ($this->news_detail ?? ''),
            'detail_plain' => (string) ($this->plain_summary ?? ''),
            'public_url' => route('frontend.researchnews.show', $this->id),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
