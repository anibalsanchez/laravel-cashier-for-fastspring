<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;

/**
 * This class is a listener for subscription deactivation events.
 * It deactivated fastspring subscription and create another local, free one.
 *
 * {@inheritdoc}
 */
class SubscriptionDeactivated extends Base
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

        // now this code only convert state into deactivated
        // you may want to do something special to your project
        // for instance you may want to turn subscription into free local package
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
