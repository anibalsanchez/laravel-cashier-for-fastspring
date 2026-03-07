<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;
use Photalika\CashierForFastspring\Models\SubscriptionPeriod;

class SubscriptionActivated
{
    public function handle(Events\SubscriptionActivated $subscriptionActivated): void
    {
        $data = $subscriptionActivated->data;

        $billable = $subscriptionActivated->billable();
        $subscriptionName = $data['tags']['name'] ?? 'default';

        $subscription = $billable->subscription();

        if (! $subscription) {
            $subscriptionModel = \Photalika\CashierForFastspring\Cashier::$subscriptionModel;
            $subscription = new $subscriptionModel;
            $subscription->billable_id = $billable->id;
            $subscription->billable_type = $billable->getMorphClass();
            $subscription->name = $subscriptionName;
        }

        $subscription->fastspring_id = $data['id'];
        $subscription->plan = $data['product']['product'];
        $subscription->state = $data['state'];
        $subscription->currency = $data['currency'];
        $subscription->quantity = $data['quantity'];
        $subscription->interval_unit = $data['intervalUnit'];
        $subscription->interval_length = $data['intervalLength'];

        $subscription->save();

        $instructions = $data['instructions'];

        foreach ($instructions as $instruction) {
            if (is_null($instruction['periodStartDateInSeconds'])) {
                continue;
            }

            if (is_null($instruction['periodEndDateInSeconds'])) {
                continue;
            }

            SubscriptionPeriod::firstOrCreate([
                'subscription_id' => $subscription->id,
                'type' => 'fastspring',
                'start_date' => date('Y-m-d', $instruction['periodStartDateInSeconds']),
                'end_date' => date('Y-m-d', $instruction['periodEndDateInSeconds']),
            ]);
        }
    }
}
