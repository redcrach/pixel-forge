<?php

namespace App\Http\Resources;

use App\Models\Generation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A generation as the History page shows it. Money stays in integer nanodollars.
 *
 * @mixin Generation
 */
class GenerationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prompt' => $this->prompt,
            'status' => $this->status->value,
            'size' => $this->size->value,
            'quality' => $this->quality->value,
            'reference_images' => $this->reference_images,
            'reference_image_count' => count($this->reference_images),
            'estimated_min_nano' => $this->meta_data['estimate']['min_nano'] ?? null,
            'estimated_max_nano' => $this->meta_data['estimate']['max_nano'] ?? null,
            'final_price_nano' => $this->final_price_nano,
            'usage' => $this->meta_data['usage'] ?? null,
            'image_url' => $this->isImageUrlExpired() ? null : $this->image_url,
            'is_image_expired' => $this->image_url !== null && $this->isImageUrlExpired(),
            'error' => $this->meta_data['error'] ?? null,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'completed_at' => $this->completed_at?->toIso8601ZuluString(),
        ];
    }
}
