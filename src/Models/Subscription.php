<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Photalika\CashierForFastspring\Fastspring\Fastspring;

/**
 * This class describes a subscription.
 *
 * {@inheritdoc}
 */
class Subscription extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'swap_at' => 'datetime',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The date on which the billing cycle should be anchored.
     *
     * @var string|null
     */
    protected $billingCycleAnchor;

    /**
     * Get the billable model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function billable()
    {
        return $this->morphTo();
    }

    /**
     * Get periods of the subscription.
     */
    public function periods()
    {
        return $this->hasMany(\Photalika\CashierForFastspring\Models\SubscriptionPeriod::class);
    }

    /**
     * Get active period of the subscription.
     */
    public function activePeriod()
    {
        return $this->hasOne(\Photalika\CashierForFastspring\Models\SubscriptionPeriod::class)
            ->where('start_date', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->where('end_date', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            ->where('type', $this->type());
    }

    /**
     * Get active period or retrieve the active period from fastspring and create.
     *
     * Note: This is not eloquent relation, it returns SubscriptionPeriod model directly.
     *
     * @return \Photalika\CashierForFastspring\Models\SubscriptionPeriod
     */
    public function activePeriodOrCreate()
    {
        if ($this->isFastspring()) {
            return $this->activeFastspringPeriodOrCreate();
        }

        return $this->activeLocalPeriodOrCreate();
    }

    /**
     * Get active fastspring period or retrieve the active period from fastspring and create.
     *
     * @return \Photalika\CashierForFastspring\Models\SubscriptionPeriod
     */
    public function activeFastspringPeriodOrCreate()
    {
        // activePeriod is not used on purpose
        // because it caches and causes confusion
        // after this method is called
        $today = Carbon::today()->format('Y-m-d');

        $activePeriod = SubscriptionPeriod::where('subscription_id', $this->id)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('type', 'fastspring')
            ->first();

        // if there is any return it
        if ($activePeriod) {
            return $activePeriod;
        }

        return $this->createPeriodFromFastspring();
    }

    /**
     * Get active local period or create.
     *
     * @return \Photalika\CashierForFastspring\Models\SubscriptionPeriod
     */
    public function activeLocalPeriodOrCreate()
    {
        // activePeriod is not used on purpose
        // because it caches and causes confusion
        // after this method is called
        $today = Carbon::today()->format('Y-m-d');

        $activePeriod = SubscriptionPeriod::where('subscription_id', $this->id)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('type', 'local')
            ->first();

        // if there is any return it
        if ($activePeriod) {
            return $activePeriod;
        }

        return $this->createPeriodLocally();
    }

    /**
     * Create period with the information from fastspring.
     *
     * @return \Photalika\CashierForFastspring\Models\SubscriptionPeriod
     */
    protected function createPeriodFromFastspring()
    {
        $response = Fastspring::getSubscriptionsEntries([$this->fastspring_id]);

        $period = [
            // there is no info related to type in the entries endpoint
            // so we assume it is regular type
            // because we create first periods (especially including trial if there is any)
            // at the subscription creation
            'type' => 'fastspring',

            // dates
            'start_date' => $response[0]->beginPeriodDate,
            'end_date' => $response[0]->endPeriodDate,
            'subscription_id' => $this->id,
        ];

        // try to find or create
        return SubscriptionPeriod::firstOrCreate($period);
    }

    /**
     * Create period for non-fastspring/local subscriptions.
     *
     * Simply finds latest and add its dates $interval_length * $interval_unit
     * If there is no subscription period, it creates a subscription period started today
     *
     *
     * @return \Photalika\CashierForFastspring\Models\SubscriptionPeriod
     *
     * @throws \Exception
     */
    protected function createPeriodLocally()
    {
        $lastPeriod = $this->periods()->orderBy('end_date', 'desc')->first();
        $today = Carbon::today();

        // there may be times subscriptionperiods not created more than
        // interval_length * interval_unit
        // For this kind of situations, we should fill the blank (actually we dont
        // have to but while we are calculating it is nice to save them)
        do {
            // add interval value to it to create next start_date
            // and sub one day to get next end_date
            switch ($this->interval_unit) {
                // fastspring adds month without overflow
                // so lets we do the same
                case 'month':
                    $start_date = $lastPeriod
                        ? $lastPeriod->start_date->addMonthsNoOverflow($this->interval_length)
                        : Carbon::now();

                    $end_date = $start_date->copy()->addMonthsNoOverflow($this->interval_length)->subDay();
                    break;

                case 'week':
                    $start_date = $lastPeriod
                        ? $lastPeriod->start_date->addWeeks($this->interval_length)
                        : Carbon::now();

                    $end_date = $start_date->copy()->addWeeks($this->interval_length)->subDay();
                    break;

                    // probably same thing with the year
                case 'year':
                    $start_date = $lastPeriod
                        ? $lastPeriod->start_date->addYearsNoOverflow($this->interval_length)
                        : Carbon::now();

                    $end_date = $start_date->copy()->addYearsNoOverflow($this->interval_length)->subDay();
                    break;

                default:
                    throw new Exception('Unexcepted interval unit: '.$subscription->interval_unit);
            }

            $subscriptionPeriodData = [
                'type' => 'local',
                'start_date' => $start_date->format('Y-m-d'),
                'end_date' => $end_date->format('Y-m-d'),
                'subscription_id' => $this->id,
            ];

            $lastPeriod = SubscriptionPeriod::firstOrCreate($subscriptionPeriodData);
        } while (! ($today->greaterThanOrEqualTo($lastPeriod->start_date)
            && $today->lessThanOrEqualTo($lastPeriod->end_date)
        ));

        return $lastPeriod;
    }

    /**
     * Determine if the subscription is valid.
     * This includes following states on fastspring: active, trial, overdue, canceled.
     * The only state that you should stop serving is deactivated state.
     */
    public function valid(): bool
    {
        return ! $this->deactivated();
    }

    /**
     * Determine if the subscription is active.
     */
    public function active(): bool
    {
        return $this->state == 'active';
    }

    /**
     * Determine if the subscription is deactivated.
     */
    public function deactivated(): bool
    {
        return $this->state == 'deactivated';
    }

    /**
     * Determine if the subscription is not paid and in wait.
     */
    public function overdue(): bool
    {
        return $this->state == 'overdue';
    }

    /**
     * Determine if the subscription is on trial.
     */
    public function trial(): bool
    {
        return $this->state == 'trial';
    }

    /**
     * Determine if the subscription is cancelled.
     *
     * Note: That doesn't mean you should stop serving. This state means
     * user ordered to cancel at end of the billing period.
     * Subscription is converted into deactivated on the start of next payment period,
     * after cancelling it.
     */
    public function canceled(): bool
    {
        return $this->state == 'canceled';
    }

    /**
     * ALIASES.
     */
    /**
     * Alias of canceled.
     */
    public function cancelled(): bool
    {
        return $this->canceled();
    }

    /**
     * Determine if the subscription is within its trial period.
     */
    public function onTrial(): bool
    {
        return $this->trial();
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     */
    public function onGracePeriod(): bool
    {
        return $this->canceled();
    }

    /**
     * Determine type of the subscription: fastspring, local.
     */
    public function type(): string
    {
        return $this->fastspring_id ? 'fastspring' : 'local';
    }

    /**
     * Determine if the subscription is local.
     */
    public function isLocal(): bool
    {
        return $this->type() === 'local';
    }

    /**
     * Determine if the subscription is fastspring.
     */
    public function isFastspring(): bool
    {
        return $this->type() === 'fastspring';
    }

    /**
     * Swap the subscription to a new Fastspring plan.
     *
     * @param  string  $plan  New plan
     * @param  bool  $prorate  Prorate
     * @param  int  $quantity  Quantity of the product
     * @param  array  $coupons  Coupons wanted to be applied
     * @return object Response of fastspring
     *
     * @throws \Exception
     */
    public function swap($plan, $prorate, $quantity = 1, $coupons = []): static
    {
        $response = Fastspring::swapSubscription($this->fastspring_id, $plan, $prorate, $quantity, $coupons);
        $status = $response->subscriptions[0];

        if ($status->result == 'success') {
            // we update subscription
            // according to prorate value
            if ($prorate) {
                // if prorate is true
                // the plan is changed immediately
                // no need to fill swap columns

                // if the plan is in the trial state
                // then delete the current period
                // because it will change immediately
                // but period won't update because it exists
                if ($this->state == 'trial') {
                    $activePeriod = $this->activePeriodOrCreate();
                    $activePeriod->delete();
                }

                $this->plan = $plan;
                $this->save();
            } else {
                // if prorate is false
                // save plan swap_to
                // because the plan will change after a while
                $activePeriod = $this->activePeriodOrCreate();

                $this->swap_to = $plan;
                $this->swap_at = $activePeriod
                    ? $activePeriod->end_date
                    : null;
                $this->save();
            }

            return $this;
        }

        throw new Exception('Swap operation failed. Response: '.json_encode($response));
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     *
     * @return object Response of fastspring
     *
     * @throws \Exception
     */
    public function cancel(): static
    {
        $response = Fastspring::cancelSubscription($this->fastspring_id);
        $status = $response->subscriptions[0];
        $subscriptionPeriod = $this->activePeriodOrCreate();

        if ($status->result == 'success') {
            $this->state = 'canceled';
            $this->swap_at = $subscriptionPeriod
                ? $subscriptionPeriod->end_date
                : null;
            $this->save();

            return $this;
        }

        throw new Exception('Cancel operation failed. Response: '.json_encode($response));
    }

    /**
     * Cancel the subscription immediately.
     *
     *
     * @return object Response of fastspring
     *
     * @throws \Exception
     */
    public function cancelNow(): static
    {
        $response = Fastspring::cancelSubscription($this->fastspring_id, ['billing_period' => 0]);
        $status = $response->subscriptions[0];

        if ($status->result == 'success') {
            // if it is canceled now
            // state should be deactivated
            $this->state = 'deactivated';
            $this->save();

            return $this;
        }

        throw new Exception('CancelNow operation failed. Response: '.json_encode($response));
    }

    /**
     * Resume the cancelled subscription.
     *
     *
     * @return object Response of fastspring
     *
     * @throws \LogicException
     * @throws \Exception
     */
    public function resume(): static
    {
        if (! $this->onGracePeriod()) {
            throw new LogicException('Unable to resume subscription that is not within grace period or not canceled.');
        }

        $response = Fastspring::uncancelSubscription($this->fastspring_id);
        $status = $response->subscriptions[0];

        if ($status->result == 'success') {
            $this->state = 'active';

            // set null swap columns
            $this->swap_at = null;
            $this->swap_to = null;

            $this->save();

            return $this;
        }

        throw new Exception('Resume operation failed. Response: '.json_encode($response));
    }
}
