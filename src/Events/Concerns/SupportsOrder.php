<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events\Concerns;

use Photalika\CashierForFastspring\Events\Models\Order;

trait SupportsOrder
{
    public function order(): Order
    {
        return new Order($this->data);
    }
}
