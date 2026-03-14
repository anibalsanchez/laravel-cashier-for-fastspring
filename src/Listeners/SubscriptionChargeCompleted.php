<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Photalika\CashierForFastspring\Events;

class SubscriptionChargeCompleted
{
    public function handle(Events\SubscriptionChargeCompleted $subscriptionChargeCompleted): void
    {
        $data = $subscriptionChargeCompleted->data;

        $invoiceModel = \Photalika\CashierForFastspring\Cashier::$invoiceModel;
        $invoice = $invoiceModel::firstOrNew([
            'fastspring_id' => $data['order']['id'],
            'type' => 'subscription',
        ]);

        $subscriptionModel = \Photalika\CashierForFastspring\Cashier::$subscriptionModel;
        $subscription = $subscriptionModel::where('fastspring_id', $data['subscription']['id'])->firstOrFail();

        $nextDate = Carbon::createFromTimestampUTC($data['subscription']['nextInSeconds']);
        $periodEndDate = $nextDate->subDay()->format('Y-m-d H:i:s');

        $methodName = 'sub'.Str::title($subscription->interval_unit).'sNoOverflow';
        $periodStartDate = $nextDate->$methodName($subscription->interval_length)->addDay()->format('Y-m-d H:i:s');

        $billable = $subscriptionChargeCompleted->billable();

        $invoice->subscription_sequence = $data['subscription']['sequence'];
        $invoice->billable_id = $billable->id;
        $invoice->billable_type = $billable->getMorphClass();
        $invoice->subscription_id = $subscription->id;
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

        $invoice->save();

        if ($subscription) {
            $subscription->state = 'active';
            $subscription->save();
        }
    }
}
