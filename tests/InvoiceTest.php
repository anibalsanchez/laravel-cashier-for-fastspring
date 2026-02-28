<?php

namespace Photalika\CashierForFastspring\Tests;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Tests\Traits\Database;
use Photalika\CashierForFastspring\Tests\Traits\Model;

class InvoiceTest extends TestCase
{
    use Database;
    use Model;

    public static function setUpBeforeClass(): void
    {
        configureEnv();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Eloquent::unguard();

        // create tables
        $this->createUsersTable();
        $this->createAccountsTable();
        $this->createSubscriptionsTable();
        $this->createSubscriptionPeriodsTable();
        $this->createInvoicesTable();
    }

    /**
     * Tests.
     */
    public function test_order(): void
    {
        $email = 'test@test-email.me';

        $user = $this->createUser([
            'email' => $email,
            'name' => 'Nombre Apellido',
            'fastspring_id' => 'fastspring_id',
        ]);

        $invoice = $user->invoices()->create([
            'fastspring_id' => 'fastspring_id',
            'type' => 'subscription',
            'subscription_display' => 'subscription_display',
            'subscription_product' => 'subscription_product',
            'subscription_sequence' => 'subscription_sequence',
            'invoice_url' => 'invoice_url',
            'total' => 0,
            'tax' => 0,
            'subtotal' => 0,
            'discount' => 0,
            'currency' => 'USD',
            'payment_type' => 'test',
            'completed' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'subscription_period_start_date' => date('Y-m-d H:i:s'),
            'subscription_period_end_date' => date('Y-m-d H:i:s'),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $invoice->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $invoice->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $invoice->subscription_period_start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $invoice->subscription_period_end_date);
        $this->assertEquals($invoice->billable->email, $email);
    }

    /**
     * Schema Helpers.
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }

    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }
}
