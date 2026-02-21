<?php

namespace Photalika\CashierForFastspring\Listeners;

use Photalika\CashierForFastspring\Events;
use Photalika\CashierForFastspring\Invoice;

class OrderCompleted extends Base
{
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(Events\OrderCompleted $orderCompleted): void
    {
        // Try to find that invoice on the database if not exists then create
        // one
        $data = $orderCompleted->data;
        $subscription = $data['items'][0]['subscription'];

        $invoice = Invoice::firstOrNew([
            'fastspring_id' => $data['id'],
            'type' => 'subscription',
        ]);

        $periodStartDate = $subscription['nextInSeconds'];
        $periodEndDate = $subscription['beginInSeconds'];

        // fill the model
        $invoice->subscription_sequence = $subscription['sequence'];
        $invoice->user_id = $this->getUserByFastspringId($data['account']['id'])->id;
        $invoice->subscription_display = $subscription['display'];
        $invoice->subscription_product = $subscription['product'];
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

        // and save
        $invoice->save();
    }
}
