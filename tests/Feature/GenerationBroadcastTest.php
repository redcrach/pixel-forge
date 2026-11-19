<?php

use App\Enums\GenerationStatus;
use App\Events\GenerationUpdated;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('a new generation is pushed to its owner\'s private channel', function () {
    Event::fake([GenerationUpdated::class]);

    $generation = Generation::factory()->create();

    Event::assertDispatched(GenerationUpdated::class, fn (GenerationUpdated $event) => $event->generation['id'] === $generation->id
        && $event->broadcastOn()->name === "private-users.{$generation->user_id}.generations");
});

test('a status change is pushed with the generation as the history page shows it', function () {
    $generation = Generation::factory()->create(['meta_data' => ['estimate' => ['min_nano' => 1, 'max_nano' => 2]]]);
    Event::fake([GenerationUpdated::class]);

    $generation->update(['status' => GenerationStatus::PROCESSING]);

    Event::assertDispatched(GenerationUpdated::class, function (GenerationUpdated $event) use ($generation) {
        $payload = $event->broadcastWith()['generation'];

        return $payload['id'] === $generation->id
            && $payload['status'] === 'processing'
            && $payload['estimated_max_nano'] === 2;
    });
});

test('each change is pushed as it was at that moment, even when the broadcasts run later', function () {
    $generation = Generation::factory()->create();
    Event::fake([GenerationUpdated::class]);

    $generation->update(['status' => GenerationStatus::PROCESSING]);
    $generation->update(['status' => GenerationStatus::SUCCEEDED]);

    $broadcastStatuses = Event::dispatched(GenerationUpdated::class)
        ->map(fn (array $dispatch) => $dispatch[0]->broadcastWith()['generation']['status'])
        ->all();

    expect($broadcastStatuses)->toBe(['processing', 'succeeded']);
});

test('saving a generation without any change pushes nothing', function () {
    $generation = Generation::factory()->create();
    Event::fake([GenerationUpdated::class]);

    $generation->save();

    Event::assertNotDispatched(GenerationUpdated::class);
});

describe('channel authorization', function () {
    beforeEach(function () {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
        ]);

        // Tests boot with the null broadcaster, so register the channels again on the Reverb one.
        require base_path('routes/channels.php');
    });

    test('a user may listen to their own generations', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => "private-users.{$user->id}.generations"])
            ->assertOk();
    });

    test('a user may not listen to someone else\'s generations', function () {
        $otherUser = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => "private-users.{$otherUser->id}.generations"])
            ->assertForbidden();
    });
});
