<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Helpers;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use Photalika\CashierForFastspring\Cashier;

class MoneyHelper
{
    public static function formatAmount($amount, $currency, $locale = null, array $options = [])
    {
        if (Cashier::$formatCurrencyUsing) {
            return call_user_func(Cashier::$formatCurrencyUsing, $amount, $currency, $locale, $options);
        }

        $money = new Money($amount, new Currency(strtoupper((string) $currency)));

        $locale ??= config('cashier.currency_locale');

        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        if (isset($options['min_fraction_digits'])) {
            $numberFormatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $options['min_fraction_digits']);
        }

        $intlMoneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies);

        return $intlMoneyFormatter->format($money);
    }
}
