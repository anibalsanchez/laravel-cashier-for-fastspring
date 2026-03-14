<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;

class OrderCompleted
{
    public function handle(Events\OrderCompleted $orderCompleted): void
    {
        $data = $orderCompleted->data;
        $eventSubscription = $data['items'][0]['subscription'];

        $invoiceModel = \Photalika\CashierForFastspring\Cashier::$invoiceModel;
        $invoice = $invoiceModel::firstOrNew([
            'fastspring_id' => $data['id'],
            'type' => 'subscription',
        ]);

        $periodStartDate = $eventSubscription['nextInSeconds'];
        $periodEndDate = $eventSubscription['beginInSeconds'];

        $billable = $orderCompleted->billable();

        $subscriptionModel = \Photalika\CashierForFastspring\Cashier::$subscriptionModel;
        $subscription = $subscriptionModel::where('fastspring_id', $eventSubscription['id'])->firstOrFail();

        $invoice->subscription_id = $subscription->id;
        $invoice->subscription_sequence = $eventSubscription['sequence'];
        $invoice->billable_id = $billable->id;
        $invoice->billable_type = $billable->getMorphClass();
        $invoice->subscription_display = $eventSubscription['display'];
        $invoice->subscription_product = $eventSubscription['product'];
        $invoice->invoice_url = $data['invoiceUrl'];
        $invoice->total = $data['total'];
        $invoice->tax = $data['tax'];
        $invoice->subtotal = $data['subtotal'];
        $invoice->discount = $data['discount'];
        $invoice->currency = $data['currency'];
        $invoice->payment_type = $data['payment']['type'];
        $invoice->completed = $data['completed'];
        $invoice->subscription_period_start_date = date('Y-m-d H:i:s', $periodStartDate);
        $invoice->subscription_period_end_date = date('Y-m-d H:i:s', $periodEndDate);

        $invoice->save();
    }
}
