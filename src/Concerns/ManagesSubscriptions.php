<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Concerns;

use Photalika\CashierForFastspring\Helpers\SubscriptionBuilder;

trait ManagesSubscriptions
{
    /**
     * Begin creating a new subscription.
     *
     * @param  string  $subscription  Subscription name
     * @param  string  $plan  The plan name
     */
    public function newSubscription($subscription): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $subscription);
    }

    /**
     * Determine if the subscription is on trial.
     *
     * @param  string  $subscription  Subscription name
     * @param  string|null  $plan  Plan name
     */
    public function onTrial($subscription = 'default', $plan = null): bool
    {
        $subscription = $this->subscription($subscription);

        if (is_null($plan)) {
            return $subscription && $subscription->onTrial();
        }

        return $subscription && $subscription->onTrial() &&
               $subscription->plan === $plan;
    }

    /**
     * Determine if the model has a given subscription.
     *
     * @param  string  $subscription  Subscription name
     * @param  string|null  $plan  Plan name
     * @return bool
     */
    public function subscribed($subscription = 'default', $plan = null)
    {
        $subscription = $this->subscription($subscription);

        if (is_null($subscription)) {
            return false;
        }

        if (is_null($plan)) {
            return $subscription->valid();
        }

        return $subscription->valid() &&
               $subscription->plan === $plan;
    }

    /**
     * Get a subscription instance by name.
     *
     * @param  string  $subscription
     * @return \Photalika\CashierForFastspring\Models\Subscription|null
     */
    public function subscription($subscription = 'default')
    {
        return $this->subscriptions()
            ->where('name', $subscription)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get all of the subscriptions for the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscriptions()
    {
        return $this->morphMany(\Photalika\CashierForFastspring\Cashier::$subscriptionModel, 'billable')->orderBy('created_at', 'desc');
    }

    /**
     * Determine if the model is actively subscribed to one of the given plans.
     *
     * @param  string|null  $plans  Plan name
     * @param  string  $subscription  Subscription name
     * @return bool
     */
    public function subscribedToPlan($plans, $subscription = 'default')
    {
        $subscription = $this->subscription($subscription);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return in_array($subscription->plan, (array) $plans, true);
    }

    /**
     * Determine if the entity is on the given plan.
     *
     * @param  string  $plan  Plan name
     */
    public function onPlan($plan): bool
    {
        return ! is_null($this->subscriptions->first(fn ($value): bool => $value->plan === $plan && $value->valid()));
    }
}
