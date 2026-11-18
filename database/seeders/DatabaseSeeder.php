<?php

namespace Database\Seeders;

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $estimate = ['min_nano' => 58_437_500, 'max_nano' => 86_637_500];

        Generation::factory()->for($user)->count(3)->create([
            'status' => GenerationStatus::SUCCEEDED,
            'meta_data' => [
                'estimate' => $estimate,
                'rates' => [
                    'input_text_tokens' => '5.00',
                    'input_image_tokens' => '10.00',
                    'output_tokens' => '40.00',
                    'reasoning_tokens' => '12.50',
                ],
                'usage' => [
                    'input_text_tokens' => 54,
                    'input_image_tokens' => 0,
                    'output_tokens' => 1100,
                    'reasoning_tokens' => 1200,
                ],
            ],
            'provider_cost_nano' => 59_270_000,
            'final_price_nano' => 74_087_500,
            'image_url' => 'https://picsum.photos/seed/pixelforge/1024/1024',
            'completed_at' => now(),
        ]);
        Generation::factory()->for($user)->create([
            'status' => GenerationStatus::FAILED,
            'meta_data' => [
                'estimate' => $estimate,
                'error' => ['code' => 'content_policy_violation', 'message' => 'The prompt was rejected by the content policy.'],
            ],
            'completed_at' => now(),
        ]);
        Generation::factory()->for($user)->create(['status' => GenerationStatus::PROCESSING, 'meta_data' => ['estimate' => $estimate]]);
        Generation::factory()->for($user)->create(['status' => GenerationStatus::QUEUED, 'meta_data' => ['estimate' => $estimate]]);
    }
}
