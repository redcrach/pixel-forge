<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\DevCommands;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureHttpClients();
        $this->configureDevCommands();
    }

    /**
     * `composer run dev` must also work the generations queue, or submitted generations never reach Pixelforge.
     */
    protected function configureDevCommands(): void
    {
        DevCommands::artisan('queue:listen --queue=generations,default --tries=1 --timeout=0', 'queue');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Register preconfigured HTTP clients for external APIs.
     *
     * Usage: Http::pixelforge()->get('/pricing')
     */
    protected function configureHttpClients(): void
    {
        Http::macro('pixelforge', fn (): PendingRequest => Http::baseUrl(config('pixelforge.base_url'))
            ->withToken((string) config('pixelforge.api_key'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(config('pixelforge.timeout'))
        );
    }
}
