<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;

/**
 * This class is a listener for subscription state change events.
 * It is planned to listen following fastspring events:
 *  - subscription.canceled
 *  - subscription.payment.overdue
 * It updates related subscription event.
 *
 * IMPORTANT: This class handles expansion enabled webhooks.
 */
class SubscriptionStateChanged
{
    /**
     * Handle the event.
     */
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
