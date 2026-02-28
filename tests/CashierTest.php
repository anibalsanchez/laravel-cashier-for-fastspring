<?php

namespace Photalika\CashierForFastspring\Tests;

use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Account;
use Photalika\CashierForFastspring\Cashier;
use Photalika\CashierForFastspring\Invoice;
use Photalika\CashierForFastspring\Subscription;
use Photalika\CashierForFastspring\Tests\Traits\Database;
use Photalika\CashierForFastspring\Tests\Traits\Model;

class CustomAccount extends Account
{
    protected $table = 'accounts';
}

class CustomSubscription extends Subscription
{
    protected $table = 'subscriptions';
}

class CustomInvoice extends Invoice
{
    protected $table = 'invoices';
}

class CashierTest extends TestCase
{
    use Database;
    use Model;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Database\Eloquent\Model::unguard();

        $this->createUsersTable();
        $this->createAccountsTable();
        $this->createSubscriptionsTable();
        $this->createSubscriptionPeriodsTable();
        $this->createInvoicesTable();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset Cashier models to default
        Cashier::useAccountModel(Account::class);
        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::useInvoiceModel(Invoice::class);
    }

    public function test_can_customize_cashier_models(): void
    {
        Cashier::useAccountModel(CustomAccount::class);
        Cashier::useSubscriptionModel(CustomSubscription::class);
        Cashier::useInvoiceModel(CustomInvoice::class);

        $this->assertEquals(CustomAccount::class, Cashier::$accountModel);
        $this->assertEquals(CustomSubscription::class, Cashier::$subscriptionModel);
        $this->assertEquals(CustomInvoice::class, Cashier::$invoiceModel);
    }

    public function test_custom_models_are_used_in_relations(): void
    {
        Cashier::useAccountModel(CustomAccount::class);
        Cashier::useSubscriptionModel(CustomSubscription::class);
        Cashier::useInvoiceModel(CustomInvoice::class);

        $user = $this->createUser(['fastspring_id' => 'custom_fastspring_id']);

        $this->assertInstanceOf(CustomAccount::class, $user->account);

        $this->createSubscription($user);
        $user->invoices()->create([
            'fastspring_id' => 'inv_id',
            'type' => 'subscription',
            'invoice_url' => 'invoice_url',
            'total' => 0,
            'tax' => 0,
            'subtotal' => 0,
            'discount' => 0,
            'currency' => 'USD',
            'payment_type' => 'test',
            'completed' => true,
        ]);

        $this->assertInstanceOf(CustomSubscription::class, $user->subscriptions()->first());
        $this->assertInstanceOf(CustomInvoice::class, $user->invoices()->first());
    }
}
