<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Tests\Helpers;

use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Cashier;
use Photalika\CashierForFastspring\Helpers\MoneyHelper;

class MoneyHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cashier::$formatCurrencyUsing = null;
    }

    public function test_it_can_format_amount(): void
    {
        config(['cashier.currency_locale' => 'en_US']);

        $formatted = MoneyHelper::formatAmount(1000, 'USD');

        $this->assertStringContainsString('10.00', $formatted);
        $this->assertStringContainsString('$', $formatted);
    }

    public function test_it_can_format_amount_with_different_currency(): void
    {
        config(['cashier.currency_locale' => 'en_US']);

        $formatted = MoneyHelper::formatAmount(1000, 'EUR');

        $this->assertStringContainsString('10.00', $formatted);
        $this->assertStringContainsString('€', $formatted);
    }

    public function test_it_can_format_amount_with_custom_locale(): void
    {
        $formatted = MoneyHelper::formatAmount(1000, 'EUR', 'de_DE');

        // In German, it's 10,00 €
        $this->assertStringContainsString('10,00', $formatted);
        $this->assertStringContainsString('€', $formatted);
    }

    public function test_it_can_format_amount_with_options(): void
    {
        config(['cashier.currency_locale' => 'en_US']);

        $formatted = MoneyHelper::formatAmount(1000, 'USD', null, ['min_fraction_digits' => 0]);

        $this->assertEquals('$10', $formatted);
    }

    public function test_it_can_format_amount_using_custom_callback(): void
    {
        Cashier::$formatCurrencyUsing = (fn ($amount, $currency, $locale, $options): string => 'CUSTOM '.$amount.' '.$currency);

        $formatted = MoneyHelper::formatAmount(1000, 'USD');

        $this->assertEquals('CUSTOM 1000 USD', $formatted);
    }
}
