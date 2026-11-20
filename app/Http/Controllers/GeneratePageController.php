<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenerationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GeneratePageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $remixId = $request->string('remix')->value();

        $remixedGeneration = empty($remixId) ? null : $request->user()?->generations()->find($remixId);

        return Inertia::render('Generate', [
            'remix' => $remixedGeneration ? GenerationResource::make($remixedGeneration)->resolve() : null,
        ]);
    }
}
