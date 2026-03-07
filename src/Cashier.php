<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring;

class Cashier
{
    public const VERSION = '1.0.0';

    /**
     * The custom currency formatter.
     *
     * @var callable
     */
    public static $formatCurrencyUsing;

    /**
     * The customer model class name.
     *
     * @var string
     */
    public static $accountModel = Account::class;

    /**
     * The subscription model class name.
     *
     * @var string
     */
    public static $subscriptionModel = Subscription::class;

    /**
     * The invoice model class name.
     *
     * @var string
     */
    public static $invoiceModel = Invoice::class;

    /**
     * Get the customer instance by its Fastspring customer ID.
     *
     * @param  string  $accountId
     * @return \Photalika\CashierForFastspring\Billable|null
     */
    public static function findBillable($accountId)
    {
        return (new static::$accountModel)->where('fastspring_id', $accountId)->first()?->billable;
    }

    /**
     * Get the Fastspring webhook url.
     *
     * @return string
     */
    public static function webhookUrl()
    {
        return config('fastspring.webhook') ?? route('fastspring.webhook');
    }

    /**
     * Set the customer model class name.
     *
     * @param  string  $accountModel
     */
    public static function useAccountModel($accountModel): void
    {
        static::$accountModel = $accountModel;
    }

    /**
     * Set the subscription model class name.
     *
     * @param  string  $subscriptionModel
     */
    public static function useSubscriptionModel($subscriptionModel): void
    {
        static::$subscriptionModel = $subscriptionModel;
    }

    /**
     * Set the invoice model class name.
     *
     * @param  string  $invoiceModel
     */
    public static function useInvoiceModel($invoiceModel): void
    {
        static::$invoiceModel = $invoiceModel;
    }
}
