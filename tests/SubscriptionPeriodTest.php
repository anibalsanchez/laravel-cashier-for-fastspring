<?php

namespace Photalika\CashierForFastspring\Tests;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Models\SubscriptionPeriod;
use Photalika\CashierForFastspring\Tests\Traits\Database;
use Photalika\CashierForFastspring\Tests\Traits\Model;

class SubscriptionPeriodTest extends TestCase
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
    public function test_subscription_period_can_be_constructed(): void
    {
        $this->assertInstanceOf(SubscriptionPeriod::class, new SubscriptionPeriod);
    }

    public function test_subscription_period_can_be_inserted(): void
    {
        $email = 'test@test-email.me';

        $user = $this->createUser(['email' => $email, 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);
        $period = $this->createSubscriptionPeriod($subscription);

        $this->assertEquals($period->subscription->id, $subscription->id);
    }
}
