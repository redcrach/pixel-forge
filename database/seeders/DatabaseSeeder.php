<?php

namespace Database\Seeders;

use App\Actions\Credits\AddCredits;
use App\Enums\CreditTransactionType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
        ]);

        // Through the ledger, so the cached balance and the transactions agree
        $addCredits = app(AddCredits::class);
        $addCredits($user, 2_000_000_000, CreditTransactionType::WELCOME);
        $addCredits($user, 10_000_000_000);
    }
}
