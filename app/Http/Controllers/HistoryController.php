<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenerationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    private const PER_PAGE = 24;

    /**
     * The signed-in user's generations, newest first, loaded page by page as they scroll.
     * Cursor pagination keeps pages stable while new generations are added at the top.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('History', [
            'generations' => Inertia::scroll(fn () => GenerationResource::collection(
                $request->user()->generations()
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->cursorPaginate(self::PER_PAGE),
            )),
        ]);
    }
}
