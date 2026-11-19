<?php

namespace App\Http\Middleware;

use App\Events\GenerationUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\LiveProp;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'generationCount' => $this->generationCount($request),
            'balanceNano' => $this->balanceNano($request),
        ];
    }

    /**
     * Live: holds, settles and releases all come with a generation change, so the header follows them.
     * Purchases reload the page props themselves.
     */
    private function balanceNano(Request $request): ?LiveProp
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        return Inertia::live(
            fn (): int => $user->balance_nano,
            on: GenerationUpdated::class,
            channel: GenerationUpdated::channelFor($user),
        );
    }

    /**
     * Live: refreshed in the browser whenever one of the user's generations is created or changes.
     */
    private function generationCount(Request $request): ?LiveProp
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        return Inertia::live(
            fn (): int => $user->generations()->count(),
            on: GenerationUpdated::class,
            channel: GenerationUpdated::channelFor($user),
        );
    }
}
