<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events\Models;

class Subscription
{
    public ?string $id = null;

    public array|string|null $subscription = null;

    public ?bool $active = null;

    public ?string $state = null;

    public ?int $changed = null;

    public ?int $changedValue = null;

    public ?int $changedInSeconds = null;

    public ?string $changedDisplay = null;

    public ?bool $live = null;

    public ?string $currency = null;

    public array|string|null $account = null;

    public array|string|null $product = null;

    public ?string $sku = null;

    public ?string $display = null;

    public ?int $quantity = null;

    public ?bool $adhoc = null;

    public ?bool $autoRenew = null;

    public ?float $price = null;

    public ?string $priceDisplay = null;

    public ?float $priceInPayoutCurrency = null;

    public ?string $priceInPayoutCurrencyDisplay = null;

    public ?float $discount = null;

    public ?string $discountDisplay = null;

    public ?float $discountInPayoutCurrency = null;

    public ?string $discountInPayoutCurrencyDisplay = null;

    public ?float $subtotal = null;

    public ?string $subtotalDisplay = null;

    public ?float $subtotalInPayoutCurrency = null;

    public ?string $subtotalInPayoutCurrencyDisplay = null;

    public ?array $attributes = null;

    public ?int $next = null;

    public ?int $nextValue = null;

    public ?int $nextInSeconds = null;

    public ?string $nextDisplay = null;

    public ?int $end = null;

    public ?int $endValue = null;

    public ?int $endInSeconds = null;

    public ?string $endDisplay = null;

    public ?int $canceledDate = null;

    public ?int $canceledDateValue = null;

    public ?int $canceledDateInSeconds = null;

    public ?string $canceledDateDisplay = null;

    public ?int $deactivationDate = null;

    public ?int $deactivationDateValue = null;

    public ?int $deactivationDateInSeconds = null;

    public ?string $deactivationDateDisplay = null;

    public ?int $sequence = null;

    public ?int $periods = null;

    public ?int $remainingPeriods = null;

    public ?int $begin = null;

    public ?int $beginValue = null;

    public ?int $beginInSeconds = null;

    public ?string $beginDisplay = null;

    public ?string $intervalUnit = null;

    public ?int $intervalLength = null;

    public ?string $nextChargeCurrency = null;

    public ?int $nextChargeDate = null;

    public ?int $nextChargeDateValue = null;

    public ?string $nextChargeDateDisplay = null;

    public ?float $nextChargeTotal = null;

    public ?string $nextChargeTotalDisplay = null;

    public ?float $nextChargeTotalInPayoutCurrency = null;

    public ?string $nextChargeTotalInPayoutCurrencyDisplay = null;

    public ?string $nextNotificationType = null;

    public ?int $nextNotificationDate = null;

    public ?int $nextNotificationDateValue = null;

    public ?int $nextNotificationDateInSeconds = null;

    public ?string $nextNotificationDateDisplay = null;

    public ?array $paymentReminder = null;

    public ?array $paymentOverdue = null;

    public ?array $cancellationSetting = null;

    public ?array $instructions = null;

    public array|string|null $order = null;

    public ?float $total = null;

    public ?string $status = null;

    public ?int $timestamp = null;

    public ?int $timestampValue = null;

    public ?int $timestampInSeconds = null;

    public ?string $timestampDisplay = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
