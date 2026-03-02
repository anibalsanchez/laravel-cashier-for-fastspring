<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;

class SubscriptionStateChanged
{
    public function handle(Events\FastSpringEvent $fastSpringEvent): void
    {
        $data = $fastSpringEvent->data;

        $subscriptionModel = \Photalika\CashierForFastspring\Cashier::$subscriptionModel;
        $subscription = $subscriptionModel::where('fastspring_id', $data['id'])->firstOrFail();

        $billable = $fastSpringEvent->billable();

        $subscription->billable_id = $billable->id;
        $subscription->billable_type = $billable->getMorphClass();
        $subscription->plan = $data['product']['product'];
        $subscription->state = $data['state'];
        $subscription->currency = $data['currency'];
        $subscription->quantity = $data['quantity'];

        $subscription->save();
    }
}
