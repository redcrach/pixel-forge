<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DownloadGenerationImageController extends Controller
{
    private const FETCH_TIMEOUT_SECONDS = 30;

    /**
     * Sends a generated image as a file download. Browsers ignore the `download` attribute on
     * links to another site, so the image is fetched from Pixelforge's signed URL and passed on.
     * Only the owner may download it: another user's id is a 404.
     */
    public function __invoke(Request $request, string $generation): Response|RedirectResponse
    {
        $ownGeneration = $request->user()->generations()->findOrFail($generation);

        if ($ownGeneration->image_url === null || $ownGeneration->isImageUrlExpired()) {
            return $this->backWithError(__('This image is no longer available to download.'));
        }

        try {
            $image = Http::timeout(self::FETCH_TIMEOUT_SECONDS)->get($ownGeneration->image_url)->throw();
        } catch (ConnectionException|RequestException) {
            return $this->backWithError(__('The image could not be downloaded. Please try again.'));
        }

        return response($image->body(), headers: [
            'Content-Type' => $image->header('Content-Type') ?: 'image/png',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                "pixelforge-{$ownGeneration->id}.png",
            ),
        ]);
    }

    private function backWithError(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => $message]);

        return back();
    }
}
