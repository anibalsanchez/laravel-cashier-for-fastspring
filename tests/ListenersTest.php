<?php

namespace Photalika\CashierForFastspring\Tests;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Events;
use Photalika\CashierForFastspring\Fastspring\Fastspring;
use Photalika\CashierForFastspring\Invoice;
use Photalika\CashierForFastspring\Listeners;
use Photalika\CashierForFastspring\Subscription;
use Photalika\CashierForFastspring\Tests\Traits\Database;
use Photalika\CashierForFastspring\Tests\Traits\Model;

class ListenersTest extends TestCase
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
        $this->createSubscriptionsTable();
        $this->createSubscriptionPeriodsTable();
        $this->createInvoicesTable();
    }

    /**
     * Tests.
     */
    public function test_order_complete_listener(): void
    {
        $this->createUser(['fastspring_id' => 'fastspring_id']);
        // retrieved from fastspring's doc
        $data = $this->payloadOfOrderCompleted('fastspring_id');

        $event = new Events\OrderCompleted('id', 'order.completed', true, true, time(), $data);
        $listener = new Listeners\OrderCompleted;
        $listener->handle($event);

        $invoice = Invoice::where('fastspring_id', $data['id'])->first();
        $this->assertNotNull($invoice);
    }

    public function test_subscription_activated_listener(): void
    {
        $this->createUser(['fastspring_id' => 'fastspring_id']);
        // retrieved from fastspring's doc
        $data = $this->payloadOfSubscriptionActivated('fastspring_id');

        $event = new Events\SubscriptionActivated('id', 'subscription.activated', true, true, time(), $data);
        $listener = new Listeners\SubscriptionActivated;
        $listener->handle($event);

        $subscription = Subscription::where('fastspring_id', $data['id'])->first();

        $this->assertNotNull($subscription);
        $this->assertEquals($subscription->periods->count(), 1);
    }

    public function test_subscription_charge_completed_listener(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_id']);
        $this->createSubscription($user, ['state' => 'overdue', 'fastspring_id' => 'subscription_id']);

        // retrieved from fastspring's doc
        $data = $this->payloadOfSubscriptionChargeCompleted('subscription_id', 'fastspring_id');

        $event = new Events\SubscriptionChargeCompleted(
            'id',
            'subscription.charge.completed',
            true,
            true,
            time(),
            $data
        );
        $listener = new Listeners\SubscriptionChargeCompleted;
        $listener->handle($event);

        $invoice = Invoice::where('fastspring_id', $data['order']['id'])->first();
        $this->assertNotNull($invoice);
    }

    public function test_subscription_state_change_listener(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_account_id']);
        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_subscription_id']);

        // retrieved from fastspring's doc
        $data = $this->payloadOfSubscriptionCanceled('fastspring_subscription_id', 'fastspring_account_id');

        $subscriptionCanceled = new Events\SubscriptionCanceled('id', 'subscription.canceled', true, true, time(), $data);
        $subscriptionStateChanged = new Listeners\SubscriptionStateChanged;
        $subscriptionStateChanged->handle($subscriptionCanceled);

        $subscription = Subscription::where('fastspring_id', $data['id'])->first();
        $this->assertNotNull($subscription);
        $this->assertEquals($subscription->state, $data['state']);
    }

    public function test_subscription_deactivated_listener(): void
    {
        $user = $this->createUser(['fastspring_id' => 'fastspring_id']);
        $subscription = $this->createSubscription($user, ['fastspring_id' => 'fastspring_subscription_id']);

        // retrieved from fastspring's doc
        $data = $this->payloadOfSubscriptionDeactivated('fastspring_id', 'fastspring_subscription_id');

        $event = new Events\SubscriptionDeactivated('id', 'subscription.activated', true, true, time(), $data);
        $listener = new Listeners\SubscriptionDeactivated;
        $listener->handle($event);

        $subscription = Subscription::where('fastspring_id', $data['id'])->first();

        $this->assertEquals($subscription->state, 'deactivated');
    }

    /**
     * Payload from fastspring.
     */
    protected function payloadOfSubscriptionCanceled($subscriptionId, $accountId): mixed
    {
        $template = file_get_contents(__DIR__.'/Payloads/subscription_canceled.json');
        $jsonString = $this->renderTemplate(
            $template,
            ['subscriptionId' => $subscriptionId, 'accountId' => $accountId]
        );

        return json_decode((string) $jsonString, true);
    }

    protected function payloadOfSubscriptionActivated($accountId): mixed
    {
        $template = file_get_contents(__DIR__.'/Payloads/subscription_activated.json');
        $jsonString = $this->renderTemplate($template, ['accountId' => $accountId]);

        return json_decode((string) $jsonString, true);
    }

    protected function payloadOfSubscriptionDeactivated($accountId, $subscriptionId): mixed
    {
        $template = file_get_contents(__DIR__.'/Payloads/subscription_deactivated.json');
        $jsonString = $this->renderTemplate($template, [
            'accountId' => $accountId,
            'subscriptionId' => $subscriptionId,
        ]);

        return json_decode((string) $jsonString, true);
    }

    protected function payloadOfSubscriptionChargeCompleted($subscriptionId, $accountId): mixed
    {
        $template = file_get_contents(__DIR__.'/Payloads/subscription_charge_completed.json');
        $jsonString = $this->renderTemplate(
            $template,
            ['subscriptionId' => $subscriptionId, 'accountId' => $accountId]
        );

        return json_decode((string) $jsonString, true);
    }

    protected function payloadOfOrderCompleted($accountId): mixed
    {
        $template = file_get_contents(__DIR__.'/Payloads/order_completed.json');
        $jsonString = $this->renderTemplate($template, ['accountId' => $accountId]);

        return json_decode((string) $jsonString, true);
    }

    protected function renderTemplate($template, $data): string|array|null
    {
        $dataKeys = array_keys($data);
        $replacements = array_values($data);

        $patterns = [];

        foreach ($dataKeys as $dataKey) {
            $patterns[] = '/{'.$dataKey.'}/';
        }

        return preg_replace($patterns, $replacements, (string) $template);
    }
}
