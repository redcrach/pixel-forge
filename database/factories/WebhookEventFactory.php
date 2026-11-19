<?php

namespace Database\Factories;

use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventId = 'evt_'.Str::upper(Str::random(26));

        return [
            'id' => $eventId,
            'type' => 'ping',
            'pixelforge_generation_id' => null,
            'payload' => ['id' => $eventId, 'object' => 'event', 'type' => 'ping'],
        ];
    }
}
