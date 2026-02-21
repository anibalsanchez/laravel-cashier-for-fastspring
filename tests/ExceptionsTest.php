<?php

namespace Photalika\CashierForFastspring\Tests;

use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Exceptions\NotImplementedException;

class ExceptionsTest extends TestCase
{
    public function test_not_implemented_exception_can_be_constructed(): void
    {
        $this->assertInstanceOf(NotImplementedException::class, new NotImplementedException);
    }
}
