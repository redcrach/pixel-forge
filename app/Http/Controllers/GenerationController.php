<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenerationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class GenerationController extends Controller
{
    /**
     * Submit a new generation. Not wired to Pixelforge yet: always answers with an error toast.
     */
    public function store(StoreGenerationRequest $request): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => __('Image generation is not available yet.')]);

        return back();
    }
}
