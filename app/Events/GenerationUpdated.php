<?php

namespace App\Events;

use App\Http\Resources\GenerationResource;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Tells the owner's open browser tabs that one of their generations was created or changed.
 * Only sent after the database commit, so the browser never sees a state that was rolled back.
 * The database stays the source of truth: a missed event is fixed by the next page load.
 */
class GenerationUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets;

    public int $userId;

    /**
     * Captured when the change happens, not when the queued broadcast runs, so each change
     * is sent as it was (two quick changes never both arrive as the later one).
     *
     * @var array<string, mixed>
     */
    public array $generation;

    public function __construct(Generation $generation)
    {
        $this->userId = $generation->user_id;
        $this->generation = GenerationResource::make($generation)->resolve();
    }

    /**
     * The private channel only this user may join (authorized in routes/channels.php).
     */
    public static function channelFor(User|int $user): PrivateChannel
    {
        $userId = $user instanceof User ? $user->id : $user;

        return new PrivateChannel("users.{$userId}.generations");
    }

    public function broadcastOn(): PrivateChannel
    {
        return self::channelFor($this->userId);
    }

    /**
     * The same shape the History page renders, so the browser can drop it straight into the list.
     *
     * @return array{generation: array<string, mixed>}
     */
    public function broadcastWith(): array
    {
        return ['generation' => $this->generation];
    }
}
