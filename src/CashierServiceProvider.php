<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring;

use Illuminate\Support\ServiceProvider;

/**
 * This class describes the Laravel Cashier Service Provider.
 *
 * {@inheritdoc}
 */
class CashierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fastspring.php' => config_path('fastspring.php'),
            ], 'fastspring-config');

            $this->publishes([
                __DIR__.'/../database/migrations/2026_02_28_000001_create_invoices_table.php' => database_path('migrations/2026_02_28_000001_create_invoices_table.php'),
                __DIR__.'/../database/migrations/2026_02_28_000002_create_subscription_periods_table.php' => database_path('migrations/2026_02_28_000002_create_subscription_periods_table.php'),
                __DIR__.'/../database/migrations/2026_02_28_000003_create_subscriptions_table.php' => database_path('migrations/2026_02_28_000003_create_subscriptions_table.php'),
                __DIR__.'/../database/migrations/2026_02_28_000004_create_accounts_table.php' => database_path('migrations/2026_02_28_000004_create_accounts_table.php'),
            ], 'fastspring-migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/fastspring.php',
            'fastspring'
        );
    }
}
