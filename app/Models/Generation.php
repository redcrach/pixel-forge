<?php

namespace App\Models;

use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use Database\Factories\GenerationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property string|null $pixelforge_id
 * @property string $prompt
 * @property ImageSize $size
 * @property ImageQuality $quality
 * @property list<string> $reference_images
 * @property GenerationStatus $status
 * @property array{
 *     estimate?: array{min_nano: int, max_nano: int},
 *     rates?: array<string, string>,
 *     usage?: array<string, int>,
 *     error?: array{code: string, message: string},
 * }|null $meta_data
 * @property int|null $provider_cost_nano
 * @property int|null $final_price_nano
 * @property string|null $image_url
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'pixelforge_id',
    'prompt',
    'size',
    'quality',
    'reference_images',
    'status',
    'meta_data',
    'provider_cost_nano',
    'final_price_nano',
    'image_url',
    'completed_at',
])]
class Generation extends Model
{
    /** @use HasFactory<GenerationFactory> */
    use HasFactory, HasUuids;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => GenerationStatus::PENDING->value,
        'reference_images' => '[]',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => ImageSize::class,
            'quality' => ImageQuality::class,
            'reference_images' => 'array',
            'status' => GenerationStatus::class,
            'meta_data' => 'array',
            'provider_cost_nano' => 'integer',
            'final_price_nano' => 'integer',
            'completed_at' => 'datetime',
        ];
    }
}
