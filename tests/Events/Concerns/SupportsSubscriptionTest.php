<?php

namespace Photalika\CashierForFastspring\Tests\Events\Concerns;

use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Events\Models\Subscription;
use Photalika\CashierForFastspring\Events\SubscriptionActivated;

class SupportsSubscriptionTest extends TestCase
{
    public function test_subscription_method_returns_a_subscription_model_with_payload_data(): void
    {
        $template = file_get_contents(__DIR__.'/../../Payloads/subscription_activated.json');
        $payload = json_decode($template, true);

        $subscriptionActivated = new SubscriptionActivated('id', 'subscription.activated', false, true, time(), $payload);

        $subscription = $subscriptionActivated->subscription();

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals('ttyyWWxQRv-P0WXUYXphzg', $subscription->id);
        $this->assertTrue($subscription->active);
        $this->assertEquals('active', $subscription->state);
        $this->assertFalse($subscription->live);
        $this->assertEquals('USD', $subscription->currency);
        $this->assertEquals('falcon-monthly-subscriptions', $subscription->product['product']);
        $this->assertEquals('furious10', $subscription->sku);
        $this->assertEquals('Falcon Monthly Subscription', $subscription->display);
        $this->assertEquals(1, $subscription->quantity);
        $this->assertFalse($subscription->adhoc);
        $this->assertTrue($subscription->autoRenew);
        $this->assertEquals(14.95, $subscription->price);
        $this->assertEquals(0.0, $subscription->discount);
        $this->assertEquals(14.95, $subscription->subtotal);
        $this->assertEquals(1504224000000, $subscription->next);
        $this->assertEquals(1532995200000, $subscription->end);
        $this->assertNull($subscription->canceledDate);
        $this->assertNull($subscription->deactivationDate);
        $this->assertEquals(1, $subscription->sequence);
        $this->assertEquals(12, $subscription->periods);
        $this->assertEquals(12, $subscription->remainingPeriods);
        $this->assertEquals(1501545600000, $subscription->begin);
        $this->assertEquals('month', $subscription->intervalUnit);
        $this->assertEquals(1, $subscription->intervalLength);
    }
}
