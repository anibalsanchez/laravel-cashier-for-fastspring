<?php

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
        $time = time();

        // publish migrations
        $this->publishes([
            __DIR__.'/../resources/migrations/create_subscriptions_table_for_cashier_fastspring.php' => sprintf(
                database_path('migrations').'/%s_create_subscriptions_table_for_cashier_fastspring.php',
                date('Y_m_d_His', $time)
            ),
            __DIR__.'/../resources/migrations/upgrade_user_table_for_cashier_fastspring.php' => sprintf(
                database_path('migrations').'/%s_upgrade_user_table_for_cashier_fastspring.php',
                date('Y_m_d_His', ++$time)
            ),
            __DIR__.'/../resources/migrations/create_invoices_table_for_cashier_fastspring.php' => sprintf(
                database_path('migrations').'/%s_create_invoices_table_for_cashier_fastspring.php',
                date('Y_m_d_His', ++$time)
            ),
            __DIR__.'/../resources/migrations/create_subscription_periods_table_for_cashier_fastspring.php' => sprintf(
                database_path('migrations').'/%s_create_subscription_periods_table_for_cashier_fastspring.php',
                date('Y_m_d_His', ++$time)
            ),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //
    }
}
