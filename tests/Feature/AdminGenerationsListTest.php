<?php

use App\Enums\GenerationStatus;
use App\Filament\Resources\Generations\GenerationResource;
use App\Filament\Resources\Generations\Pages\ManageGenerations;
use App\Models\Generation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('regular users cannot open the generations list', function () {
    $this->actingAs(User::factory()->create())
        ->get(GenerationResource::getUrl())
        ->assertForbidden();
});

test('admins see every user\'s generations with their cost', function () {
    $generation = Generation::factory()->create([
        'status' => GenerationStatus::SUCCEEDED,
        'provider_cost_nano' => 343_675_000,
        'final_price_nano' => 429_593_750,
        'completed_at' => now(),
    ]);
    $someoneElses = Generation::factory()->create();

    $this->get(GenerationResource::getUrl())->assertOk();

    Livewire::test(ManageGenerations::class)
        ->assertCanSeeTableRecords([$generation, $someoneElses])
        ->assertSee('$0.34368')
        ->assertSee('$0.42959');
});

test('generations can be filtered by status, user and creation date', function () {
    $customer = User::factory()->create();
    $failed = Generation::factory()->for($customer)->create(['status' => GenerationStatus::FAILED, 'created_at' => '2026-11-19 10:00:00']);
    $succeeded = Generation::factory()->for($customer)->create(['status' => GenerationStatus::SUCCEEDED, 'created_at' => '2026-11-18 10:00:00']);
    $someoneElses = Generation::factory()->create(['status' => GenerationStatus::FAILED, 'created_at' => '2026-11-19 10:00:00']);

    Livewire::test(ManageGenerations::class)
        ->filterTable('status', [GenerationStatus::FAILED->value])
        ->assertCanSeeTableRecords([$failed, $someoneElses])
        ->assertCanNotSeeTableRecords([$succeeded])
        ->resetTableFilters()
        ->filterTable('user', $customer->id)
        ->assertCanSeeTableRecords([$failed, $succeeded])
        ->assertCanNotSeeTableRecords([$someoneElses])
        ->resetTableFilters()
        ->filterTable('created_at', ['from' => '2026-11-19', 'until' => '2026-11-19'])
        ->assertCanSeeTableRecords([$failed, $someoneElses])
        ->assertCanNotSeeTableRecords([$succeeded]);
});

test('the list is read-only', function () {
    $generation = Generation::factory()->create();

    expect(GenerationResource::canCreate())->toBeFalse()
        ->and(GenerationResource::canEdit($generation))->toBeFalse()
        ->and(GenerationResource::canDelete($generation))->toBeFalse();

    Livewire::test(ManageGenerations::class)
        ->assertTableActionExists('view')
        ->assertTableActionDoesNotExist('edit')
        ->assertTableActionDoesNotExist('delete');
});
