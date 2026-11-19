<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenerationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowGenerationController extends Controller
{
    /**
     * One generation on its own page: the image, its prompt and settings, and what it cost.
     * Looked up through the signed-in user's generations, so anyone else's id is a 404.
     */
    public function __invoke(Request $request, string $generation): Response
    {
        return Inertia::render('generations/Show', [
            'generation' => GenerationResource::make(
                $request->user()->generations()->findOrFail($generation),
            )->resolve(),
        ]);
    }
}
