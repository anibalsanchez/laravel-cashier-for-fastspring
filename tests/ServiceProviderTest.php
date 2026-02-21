<?php

namespace Photalika\CashierForFastspring\Tests;

use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\CashierServiceProvider;

class ServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tests.
     */
    public function test_service_provider_can_be_constructed(): void
    {
        $this->assertInstanceOf(CashierServiceProvider::class, new CashierServiceProvider(app()));
    }

    public function test_register_method(): void
    {
        $cashierServiceProvider = new CashierServiceProvider(app());
        $this->assertNull($cashierServiceProvider->register());
    }

    // TODO: should test if it moves migration files or not
    public function test_boot_method(): void
    {
        $cashierServiceProvider = new CashierServiceProvider(app());
        $this->assertNull($cashierServiceProvider->boot());
    }
}
