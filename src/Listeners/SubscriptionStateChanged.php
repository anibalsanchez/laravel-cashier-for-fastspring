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
 *
 * {@inheritdoc}
 */
class SubscriptionStateChanged extends Base
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Events\Base $base): void
    {
        $data = $base->data;

        // create
        $subscriptionModel = \Photalika\CashierForFastspring\Cashier::$subscriptionModel;
        $subscription = $subscriptionModel::where('fastspring_id', $data['id'])->firstOrFail();

        $billable = $this->getBillableByFastspringId($data['account']['id']);

        // fill
        $subscription->billable_id = $billable->id;
        $subscription->billable_type = $billable->getMorphClass();
        $subscription->plan = $data['product']['product'];
        $subscription->state = $data['state'];
        $subscription->currency = $data['currency'];
        $subscription->quantity = $data['quantity'];

        // save
        $subscription->save();
    }
}
