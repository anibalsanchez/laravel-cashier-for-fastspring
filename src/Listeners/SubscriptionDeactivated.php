<?php

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;
use Photalika\CashierForFastspring\Subscription;

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
        $subscription = Subscription::where('fastspring_id', $data['id'])->firstOrFail();

        // fill
        $subscription->user_id = $this->getUserByFastspringId($data['account']['id'])->id;
        $subscription->plan = $data['product']['product'];
        $subscription->state = $data['state'];
        $subscription->currency = $data['currency'];
        $subscription->quantity = $data['quantity'];

        // save
        $subscription->save();
    }
}
