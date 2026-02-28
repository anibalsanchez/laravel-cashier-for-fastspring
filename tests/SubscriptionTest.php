<?php

namespace Photalika\CashierForFastspring\Tests;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Subscription;
use Photalika\CashierForFastspring\Tests\Traits\Database;
use Photalika\CashierForFastspring\Tests\Traits\Guzzle;
use Photalika\CashierForFastspring\Tests\Traits\Model;

class SubscriptionTest extends TestCase
{
    use Database;
    use Guzzle;
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
    public function test_subscription_can_be_constructed(): void
    {
        $this->assertInstanceOf(Subscription::class, new Subscription);
    }

    public function test_billable(): void
    {
        $email = 'test@test-email.me';

        $user = $this->createUser(['email' => $email, 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);

        $this->assertEquals($subscription->billable->email, $email);
    }

    public function test_periods(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);

        // create two periods
        $period1 = $this->createSubscriptionPeriod($subscription);
        $period2 = $this->createSubscriptionPeriod($subscription);

        $this->assertEquals($subscription->periods->count(), 2);
        $this->assertEquals($subscription->periods[0]->id, $period1->id);
        $this->assertEquals($subscription->periods[1]->id, $period2->id);
    }

    public function test_active_local_period(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);

        $activePeriod = $this->createSubscriptionPeriod($subscription, [
            'subscription_id' => 'fastspring_id',
            'start_date' => $today,
            'end_date' => $today,
            'type' => 'local',
        ]);

        $this->assertNotNull($activePeriod);
        $this->assertNotNull($subscription);
        $this->assertEquals($activePeriod['subscription_id'], $activePeriod->id);
        $this->assertEquals($subscription->id, $activePeriod->id);
    }

    public function test_active_period_or_create_while_there_is_one(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);

        // create two periods
        $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(45)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'type' => 'fastspring',
        ]);

        $period2 = $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(16)->format('Y-m-d'),
            'type' => 'fastspring',
        ]);

        $activePeriod = $subscription->activePeriodOrCreate();

        $this->assertNotNull($activePeriod);
        $this->assertEquals($activePeriod->id, $period2->id);
        $this->assertEquals($subscription->activePeriod->id, $activePeriod->id);
    }

    public function test_active_period_or_create_while_there_is_none_for_fastspring_subscription(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);

        // set response for entry api
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                [
                    'beginPeriodDate' => Carbon::now()->subDays(14)->format('Y-m-d'),
                    'endPeriodDate' => Carbon::now()->addDays(16)->format('Y-m-d'),
                ],
            ])),
        ]);

        // create two periods
        $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(45)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
        ]);

        $period2 = $subscription->activePeriodOrCreate();

        $this->assertNotNull($period2);
        $this->assertEquals($subscription->periods->count(), 2);
        // activePeriodOrCreate
        $this->assertEquals($subscription->activePeriod->id, $period2->id);
    }

    public function test_active_period_or_create_while_there_is_early_one_for_local_monthly_subscription(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, [
            'state' => 'active',
            'interval_unit' => 'month',
            'fastspring_id' => null,
        ]);

        // create a period with a start_date 65 days ago
        $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(65)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(35)->format('Y-m-d'),
        ]);

        $lastPeriod = $subscription->activePeriodOrCreate();

        $this->assertNotNull($lastPeriod);
        // now there must be 4 total periods
        $this->assertEquals($subscription->periods->count(), 3);
        // activePeriodOrCreate
        $this->assertEquals($subscription->activePeriod->id, $lastPeriod->id);
    }

    public function test_active_period_or_create_while_there_is_early_one_for_local_weekly_subscription(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, [
            'state' => 'active',
            'interval_unit' => 'week',
            'fastspring_id' => null,
        ]);

        // create a period with a start_date 65 days ago
        $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(65)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(35)->format('Y-m-d'),
        ]);

        $lastPeriod = $subscription->activePeriodOrCreate();

        $this->assertNotNull($lastPeriod);
        // now there must be 10 total periods
        $this->assertEquals($subscription->periods->count(), 10);
        // activePeriodOrCreate
        $this->assertEquals($subscription->activePeriod->id, $lastPeriod->id);
    }

    public function test_active_period_or_create_while_there_is_early_one_for_local_yearly_subscription(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, [
            'state' => 'active',
            'interval_unit' => 'year',
            'fastspring_id' => null,
        ]);

        // create a period
        $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(465)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(100)->format('Y-m-d'),
        ]);

        $lastPeriod = $subscription->activePeriodOrCreate();

        $this->assertNotNull($lastPeriod);
        // now there must be 2 total periods
        $this->assertEquals($subscription->periods->count(), 2);
        // activePeriodOrCreate
        $this->assertEquals($subscription->activePeriod->id, $lastPeriod->id);
    }

    public function test_active_period_or_create_while_there_is_none_for_local_subscription(): void
    {
        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, [
            'state' => 'active',
            'fastspring_id' => null,
        ]);

        $activePeriod = $subscription->activePeriodOrCreate();

        $this->assertNotNull($activePeriod);
        // now there must be 4 total periods
        $this->assertEquals($subscription->periods->count(), 1);
        // activePeriodOrCreate
        $this->assertEquals($subscription->activePeriod->id, $activePeriod->id);
        // active period start_date
        $this->assertEquals($subscription->activePeriod->start_date, Carbon::today());
    }

    public function test_active_period_or_create_with_non_exist_interval_unit(): void
    {
        $this->expectException(\Exception::class);

        $user = $this->createUser(['email' => 'test@test-email.me', 'fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, [
            'state' => 'active',
            'interval_unit' => 'none',
            'fastspring_id' => null,
        ]);

        // create a period
        $this->createSubscriptionPeriod($subscription, [
            'start_date' => Carbon::now()->subDays(465)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(100)->format('Y-m-d'),
        ]);

        $subscription->activePeriodOrCreate();
    }

    public function test_active_subscription(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'active']);

        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->active());

        $this->assertFalse($subscription->deactivated());
        $this->assertFalse($subscription->overdue());
        $this->assertFalse($subscription->trial());
        $this->assertFalse($subscription->canceled());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->onGracePeriod());
    }

    public function test_canceled_subscription(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'canceled']);

        $this->assertTrue($subscription->canceled());
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->onGracePeriod());

        $this->assertFalse($subscription->deactivated());
        $this->assertFalse($subscription->overdue());
        $this->assertFalse($subscription->trial());
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onTrial());
    }

    public function test_on_trial_subscription(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'trial']);

        $this->assertTrue($subscription->trial());
        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->onTrial());
        $this->assertTrue($subscription->onTrial('default', 'starter-plan'));

        $this->assertFalse($subscription->deactivated());
        $this->assertFalse($subscription->overdue());
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onGracePeriod());
    }

    public function test_overdue_subscription(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['state' => 'overdue']);

        $this->assertTrue($subscription->overdue());
        $this->assertTrue($subscription->valid());

        $this->assertFalse($subscription->deactivated());
        $this->assertFalse($subscription->trial());
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->onGracePeriod());
    }

    public function test_swap(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'success',
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->swap('new_plan', true);

        $this->assertEquals($subscription->plan, 'new_plan');
    }

    public function test_swap_no_prorate(): void
    {
        $endDate = Carbon::now()->addDays(16)->format('Y-m-d');

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'success',
                    ],
                ],
            ])),
            new Response(200, [], json_encode([
                [
                    'beginPeriodDate' => Carbon::now()->subDays(14)->format('Y-m-d'),
                    'endPeriodDate' => $endDate,
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->swap('new_plan', false);
        $subscription->activePeriodOrCreate();

        $this->assertEquals($subscription->swap_to, 'new_plan');
        $this->assertEquals($subscription->swap_at->format('Y-m-d'), $endDate);
    }

    public function test_swap_exception(): void
    {
        $this->expectException(\Exception::class);

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'error',
                    ],
                ],
            ])),
            new Response(200, [], json_encode([
                [
                    'beginPeriodDate' => Carbon::now()->subDays(14)->format('Y-m-d'),
                    'endPeriodDate' => $endDate,
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->swap('new_plan', false);
        $subscription->activePeriodOrCreate();
    }

    public function test_cancel(): void
    {
        $endDate = Carbon::now()->addDays(16)->format('Y-m-d');

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'success',
                    ],
                ],
            ])),
            new Response(200, [], json_encode([
                [
                    'beginPeriodDate' => Carbon::now()->subDays(14)->format('Y-m-d'),
                    'endPeriodDate' => $endDate,
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->cancel();
        $this->assertEquals($subscription->state, 'canceled');
        $this->assertEquals($subscription->swap_at->format('Y-m-d'), $endDate);
    }

    public function test_cancel_exception(): void
    {
        $this->expectException(\Exception::class);

        $endDate = Carbon::now()->addDays(16)->format('Y-m-d');

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'error',
                    ],
                ],
            ])),
            new Response(200, [], json_encode([
                [
                    'beginPeriodDate' => Carbon::now()->subDays(14)->format('Y-m-d'),
                    'endPeriodDate' => $endDate,
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->cancel();
    }

    public function test_cancel_now(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'success',
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->cancelNow();
        $this->assertEquals($subscription->state, 'deactivated');
    }

    public function test_cancel_now_exception(): void
    {
        $this->expectException(\Exception::class);

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'error',
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $subscription->cancelNow();
    }

    public function test_resume(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'success',
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id', 'state' => 'canceled']);

        $subscription->resume();
        $this->assertEquals($subscription->state, 'active');
        $this->assertNull($subscription->swap_at);
        $this->assertNull($subscription->swap_to);
    }

    public function test_try_to_resume_noncanceled(): void
    {
        $this->expectException(\LogicException::class);

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    ['subscription' => 'fastspring_id'],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);

        $response = $subscription->resume();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('subscription', $response->subscriptions[0]);
    }

    public function test_resume_exception(): void
    {
        $this->expectException(\Exception::class);

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'subscriptions' => [
                    [
                        'subscription' => 'fastspring_id',
                        'result' => 'error',
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id', 'state' => 'canceled']);

        $subscription->resume();
    }

    public function test_type(): void
    {
        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $fastspringSubscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_id']);
        $localSubscription = $this->createSubscription($user, ['fastspring_id' => null]);

        // test fastspring
        $this->assertEquals($fastspringSubscription->type(), 'fastspring');
        $this->assertTrue($fastspringSubscription->isFastspring());
        $this->assertFalse($fastspringSubscription->isLocal());

        // test local
        $this->assertEquals($localSubscription->type(), 'local');
        $this->assertFalse($localSubscription->isFastspring());
        $this->assertTrue($localSubscription->isLocal());
    }
}
