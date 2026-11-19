<?php

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

function succeededGenerationWithImage(User $user, array $overrides = []): Generation
{
    return Generation::factory()->for($user)->create([
        'status' => GenerationStatus::SUCCEEDED,
        'image_url' => 'https://pixelforge.promptedit.dev/images/gen_1.png?signature=abc',
        'completed_at' => now()->subHour(),
        ...$overrides,
    ]);
}

test('the owner downloads the image as a file', function () {
    $user = User::factory()->create();
    $generation = succeededGenerationWithImage($user);
    Http::fake(['pixelforge.promptedit.dev/images/*' => Http::response('png-bytes', 200, ['Content-Type' => 'image/png'])]);

    $response = $this->actingAs($user)->get(route('generations.image.download', $generation->id));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertDownload("pixelforge-{$generation->id}.png");
    expect($response->getContent())->toBe('png-bytes');
});

test('someone else\'s image cannot be downloaded', function () {
    $generation = succeededGenerationWithImage(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->get(route('generations.image.download', $generation->id))
        ->assertNotFound();
});

test('an expired image is not fetched and the user is told why', function () {
    $user = User::factory()->create();
    $generation = succeededGenerationWithImage($user, ['completed_at' => now()->subHours(25)]);

    $this->actingAs($user)
        ->from(route('history'))
        ->get(route('generations.image.download', $generation->id))
        ->assertRedirect(route('history'))
        ->assertInertiaFlash('toast.type', 'error');

    Http::assertNothingSent();
});

test('a failed fetch from pixelforge sends the user back with an error', function () {
    $user = User::factory()->create();
    $generation = succeededGenerationWithImage($user);
    Http::fake(['pixelforge.promptedit.dev/images/*' => Http::response('Forbidden', 403)]);

    $this->actingAs($user)
        ->from(route('history'))
        ->get(route('generations.image.download', $generation->id))
        ->assertRedirect(route('history'))
        ->assertInertiaFlash('toast.type', 'error');
});
