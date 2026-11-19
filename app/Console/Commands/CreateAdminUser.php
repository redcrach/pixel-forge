<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('admin:create
    {--email=admin@pixelforge.test : Email of the admin account (an existing user is promoted)}
    {--name=Pixelforge Admin : Name for a new account}
    {--password= : Password to set (a new account without one gets a random password, printed once)}')]
#[Description('Create an admin user, or promote an existing one, for the /admin panel')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $user = User::firstOrNew(['email' => $this->option('email')]);
        $isNewUser = ! $user->exists;
        $password = $this->passwordToSet($isNewUser);

        $user->forceFill([
            'name' => $user->name ?? $this->option('name'),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'is_admin' => true,
        ]);

        if ($password !== null) {
            $user->password = $password;
        }

        $user->save();

        $this->components->info($isNewUser ? 'Admin user created.' : 'Existing user promoted to admin.');
        $this->components->twoColumnDetail('Email', $user->email);
        $this->components->twoColumnDetail('Password', $password ?? '(unchanged)');

        return self::SUCCESS;
    }

    /**
     * An existing user keeps their password unless a new one is passed explicitly.
     */
    private function passwordToSet(bool $isNewUser): ?string
    {
        $password = $this->option('password');

        if (is_string($password) && $password !== '') {
            return $password;
        }

        return $isNewUser ? Str::password(16) : null;
    }
}
