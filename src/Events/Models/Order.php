<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events\Models;

class Order
{
    public ?string $order = null;

    public ?string $id = null;

    public ?string $reference = null;

    public ?string $buyerReference = null;

    public ?bool $completed = null;

    public ?int $changed = null;

    public ?int $changedValue = null;

    public ?int $changedInSeconds = null;

    public ?string $changedDisplay = null;

    public ?string $language = null;

    public ?bool $live = null;

    public ?string $currency = null;

    public ?string $payoutCurrency = null;

    public ?string $invoiceUrl = null;

    public ?array $account = null;

    public ?float $total = null;

    public ?string $totalDisplay = null;

    public ?float $totalInPayoutCurrency = null;

    public ?string $totalInPayoutCurrencyDisplay = null;

    public ?float $tax = null;

    public ?string $taxDisplay = null;

    public ?float $taxInPayoutCurrency = null;

    public ?string $taxInPayoutCurrencyDisplay = null;

    public ?float $subtotal = null;

    public ?string $subtotalDisplay = null;

    public ?float $subtotalInPayoutCurrency = null;

    public ?string $subtotalInPayoutCurrencyDisplay = null;

    public ?float $discount = null;

    public ?string $discountDisplay = null;

    public ?float $discountInPayoutCurrency = null;

    public ?string $discountInPayoutCurrencyDisplay = null;

    public ?float $discountWithTax = null;

    public ?string $discountWithTaxDisplay = null;

    public ?float $discountWithTaxInPayoutCurrency = null;

    public ?string $discountWithTaxInPayoutCurrencyDisplay = null;

    public ?string $billDescriptor = null;

    public ?array $payment = null;

    public ?array $customer = null;

    public ?array $address = null;

    public ?array $notes = null;

    public ?array $items = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
