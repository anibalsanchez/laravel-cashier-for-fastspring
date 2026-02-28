<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Photalika\CashierForFastspring\Events;

/**
 * This class is a listener for subscription charge completed events.
 * It updates or creates related order model so that you can show payment
 * and bill details to your billables.
 *
 * IMPORTANT: This class handles expansion enabled webhooks.
 *
 * {@inheritdoc}
 */
class SubscriptionChargeCompleted extends Base
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
    public function handle(Events\SubscriptionChargeCompleted $subscriptionChargeCompleted): void
    {
        // when subscription charge completed event is triggered
        // try to find that order on the database
        // if not exists then create one
        $data = $subscriptionChargeCompleted->data;

        $invoiceModel = \Photalika\CashierForFastspring\Cashier::$invoiceModel;
        $invoice = $invoiceModel::firstOrNew([
            'fastspring_id' => $data['order']['id'],
            'type' => 'subscription',
        ]);

        // retrieve subscription to change state of it
        $subscriptionModel = \Photalika\CashierForFastspring\Cashier::$subscriptionModel;
        $subscription = $subscriptionModel::where('fastspring_id', $data['subscription']['id'])->first();

        // unfortunately fastspring does not provide subscription
        // dates with this event event their doc says it provides
        // we need to calculate ourselves
        $nextDate = Carbon::createFromTimestampUTC($data['subscription']['nextInSeconds']);
        $periodEndDate = $nextDate->subDay()->format('Y-m-d H:i:s');

        // yeap, weird way
        $methodName = 'sub'.Str::title($subscription->interval_unit).'sNoOverflow';
        $periodStartDate = $nextDate->$methodName($subscription->interval_length)->addDay()->format('Y-m-d H:i:s');

        $billable = $this->getBillableByFastspringId($data['account']['id']);

        // fill the model
        $invoice->subscription_sequence = $data['subscription']['sequence'];
        $invoice->billable_id = $billable->id;
        $invoice->billable_type = $billable->getMorphClass();
        $invoice->subscription_display = $data['subscription']['display'];
        $invoice->subscription_product = $data['subscription']['product'];
        $invoice->invoice_url = $data['order']['invoiceUrl'];
        $invoice->total = $data['order']['total'];
        $invoice->tax = $data['order']['tax'];
        $invoice->subtotal = $data['order']['subtotal'];
        $invoice->discount = $data['order']['discount'];
        $invoice->currency = $data['order']['currency'];
        $invoice->payment_type = $data['order']['payment']['type'];
        $invoice->completed = $data['order']['completed'];
        $invoice->subscription_period_start_date = $periodStartDate;
        $invoice->subscription_period_end_date = $periodEndDate;

        // and save
        $invoice->save();

        if ($subscription) {
            $subscription->state = 'active';
            $subscription->save();
        }
    }
}
