<?php

namespace Photalika\CashierForFastspring\Tests\Traits;

use Illuminate\Support\Facades\Schema;

trait Database
{
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::drop('users');
        Schema::drop('accounts');
        Schema::drop('subscriptions');
        Schema::drop('invoices');

        parent::tearDown();
    }

    public function createUsersTable(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('email');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function createAccountsTable(): void
    {
        Schema::create('accounts', function ($table): void {
            $table->id();
            $table->foreignId('billable_id');
            $table->string('billable_type');
            $table->string('fastspring_id')->unique();
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('language')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
        });
    }

    public function createSubscriptionsTable(): void
    {
        Schema::create('subscriptions', function ($table): void {
            $table->id();
            $table->foreignId('billable_id');
            $table->string('billable_type');
            $table->string('name');
            $table->string('fastspring_id')->nullable();
            $table->string('plan');
            $table->string('state');
            $table->integer('quantity');
            $table->string('currency');
            $table->string('interval_unit');
            $table->integer('interval_length');
            $table->string('swap_to')->nullable();
            $table->datetime('swap_at')->nullable();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
        });
    }

    public function createSubscriptionPeriodsTable(): void
    {
        Schema::create('subscription_periods', function ($table): void {
            $table->id();
            $table->unsignedInteger('subscription_id');
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    public function createInvoicesTable(): void
    {
        Schema::create('invoices', function ($table): void {
            $table->id();
            $table->foreignId('billable_id');
            $table->string('billable_type');
            $table->string('fastspring_id')->unique()->nullable();
            $table->string('type')->nullable(); // subscription, order
            $table->foreignId('subscription_id')->nullable();
            $table->string('subscription_display')->nullable();
            $table->string('subscription_product')->nullable();
            $table->integer('subscription_sequence')->nullable();
            $table->string('invoice_url');
            $table->decimal('total', 8, 2);
            $table->decimal('tax', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('discount', 8, 2);
            $table->string('currency');
            $table->string('payment_type');
            $table->boolean('completed');
            $table->datetime('subscription_period_start_date')->nullable();
            $table->datetime('subscription_period_end_date')->nullable();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
        });
    }
}
