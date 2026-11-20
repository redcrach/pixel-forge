<?php

use App\Actions\Reports\BuildDailyUsageReport;
use App\Dto\Reports\DailyUsageDto;
use App\Filament\Pages\UsageReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-19 12:00:00', 'UTC'));
});

test('regular users cannot open the usage report', function () {
    $this->actingAs(User::factory()->create())
        ->get(UsageReport::getUrl())
        ->assertForbidden();
});

test('it shows the last 14 UTC days for all users by default', function () {
    $this->mock(BuildDailyUsageReport::class)
        ->shouldReceive('__invoke')
        ->withArgs(fn (CarbonImmutable $startDate, CarbonImmutable $endDate, ?int $userId): bool => $startDate->toDateString() === '2026-11-06'
            && $endDate->toDateString() === '2026-11-19'
            && $userId === null)
        ->andReturn([new DailyUsageDto(
            date: '2026-11-19',
            generations: 2,
            userSpendNano: 375_000_000,
            providerCostNano: 300_000_000,
            inputTextTokens: 20,
            inputImageTokens: 1530,
            outputTokens: 2200,
            reasoningTokens: 12345,
        )]);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(UsageReport::class)
        ->assertOk()
        ->assertSee('$0.375000000')
        ->assertSee('$0.300000000')
        ->assertSee('$0.075000000')
        ->assertSee('1,530')
        ->assertSee('12,345');
});

test('the period and user filters are passed to the report', function () {
    $customer = User::factory()->create();
    $requests = [];
    $this->mock(BuildDailyUsageReport::class)
        ->shouldReceive('__invoke')
        ->andReturnUsing(function (CarbonImmutable $startDate, CarbonImmutable $endDate, ?int $userId) use (&$requests): array {
            $requests[] = [$startDate->toDateString(), $endDate->toDateString(), $userId];

            return [];
        });

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(UsageReport::class)
        ->filterTable('period', ['from' => '2026-11-19', 'until' => '2026-11-01'])
        ->filterTable('user', $customer->id);

    expect(end($requests))->toBe(['2026-11-01', '2026-11-19', $customer->id]);
});
