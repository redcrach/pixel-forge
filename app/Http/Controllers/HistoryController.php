<?php

namespace App\Http\Controllers;

use App\Enums\GenerationStatus;
use App\Enums\HistoryStatusFilter;
use App\Events\GenerationUpdated;
use App\Http\Resources\GenerationResource;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    private const PER_PAGE = 24;

    /**
     * The signed-in user's generations, filtered by status tab and prompt search, loaded page by page.
     * Cursor pagination keeps pages stable while new generations are added at the top.
     * Newest first unless `is_desc` is false. An unknown status tab falls back to All.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $statusFilter = $request->enum('status', HistoryStatusFilter::class) ?? HistoryStatusFilter::ALL;
        $isDescending = $request->boolean('is_desc', true);
        $search = $request->string('search')->trim()->limit(200, '')->value();

        return Inertia::render('History', [
            'filters' => [
                'status' => $statusFilter->value,
                'search' => $search,
                'is_desc' => $isDescending,
            ],
            'counts' => Inertia::live(
                fn (): array => $this->countsPerStatusFilter($user, $search),
                on: GenerationUpdated::class,
                channel: GenerationUpdated::channelFor($user),
            ),
            'generations' => Inertia::scroll(fn () => GenerationResource::collection(
                $this->searchedGenerations($user, $search)
                    ->whereIn('status', $statusFilter->statuses())
                    ->orderBy('created_at', $isDescending ? 'desc' : 'asc')
                    ->orderBy('id', $isDescending ? 'desc' : 'asc')
                    ->cursorPaginate(self::PER_PAGE)
                    ->withQueryString(),
            )),
        ]);
    }

    /**
     * @return HasMany<Generation, User>
     */
    private function searchedGenerations(User $user, string $search): HasMany
    {
        return $user->generations()
            ->when($search !== '', fn ($query) => $query->whereLike('prompt', "%{$search}%"));
    }

    /**
     * How many generations each status tab holds, for the current search.
     *
     * @return array<string, int>
     */
    private function countsPerStatusFilter(User $user, string $search): array
    {
        $countsPerStatus = $this->searchedGenerations($user, $search)
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [];

        foreach (HistoryStatusFilter::cases() as $statusFilter) {
            $counts[$statusFilter->value] = collect($statusFilter->statuses())
                ->sum(fn (GenerationStatus $status): int => (int) ($countsPerStatus[$status->value] ?? 0));
        }

        return $counts;
    }
}
