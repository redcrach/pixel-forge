<?php

use App\Actions\ApplyGenerationUpdate;
use App\Actions\Credits\AddCredits;
use App\Actions\Credits\HoldCredits;
use App\Jobs\SubmitGeneration;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * A Pixelforge API response saved in tests/Fixtures/pixelforge, decoded.
 *
 * @return array<string, mixed>
 */
function pixelforgeFixture(string $name): array
{
    return json_decode(file_get_contents(base_path("tests/Fixtures/pixelforge/{$name}.json")), true);
}

/**
 * A Pixelforge-Signature header for the raw body, signed like Pixelforge does.
 */
function pixelforgeSignature(string $rawBody, ?int $timestamp = null, ?string $secret = null): string
{
    $timestamp ??= now()->getTimestamp();
    $signature = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret ?? config('pixelforge.webhook_secret'));

    return "t={$timestamp},v1={$signature}";
}

/**
 * Posts a raw webhook body to our endpoint, signed correctly unless another signature is given.
 */
function postPixelforgeWebhook(string $rawBody, ?string $signature = null): TestResponse
{
    return test()->call(
        method: 'POST',
        uri: route('webhooks.pixelforge'),
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PIXELFORGE_SIGNATURE' => $signature ?? pixelforgeSignature($rawBody),
        ],
        content: $rawBody,
    );
}

/**
 * Runs the job the way a worker would, with queue interactions (release, fail) faked.
 */
function runSubmitGeneration(Generation $generation): SubmitGeneration
{
    $job = (new SubmitGeneration($generation))->withFakeQueueInteractions();
    $job->handle(app(ApplyGenerationUpdate::class));

    return $job;
}

/**
 * The raw body of a Pixelforge event wrapping the given generation object.
 */
function pixelforgeEventBody(string $eventId, string $type, array $generation): string
{
    return json_encode([
        'id' => $eventId,
        'object' => 'event',
        'type' => $type,
        'created_at' => '2026-11-04T14:03:29Z',
        'data' => ['object' => $generation],
    ]);
}

/**
 * The succeeded fixture, pointing back at our generation like a real event would.
 */
function succeededGenerationFor(Generation $generation): array
{
    return [...pixelforgeFixture('generation-succeeded'), 'metadata' => ['order_id' => $generation->id]];
}

/**
 * A user with $1.00 and one submitted generation holding its $0.10 maximum.
 */
function userWithHeldGeneration(int $balanceNano = 1_000_000_000, int $maximumCostNano = 100_000_000): Generation
{
    $user = User::factory()->create();
    app(AddCredits::class)($user, $balanceNano);
    $generation = Generation::factory()->for($user)->create();
    app(HoldCredits::class)($generation, $maximumCostNano);

    return $generation;
}

function ledgerSumNano(User $user): int
{
    return (int) $user->creditTransactions()->sum('amount_nano');
}
