<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Photalika\CashierForFastspring\Events\Models\Order;

class OrderCompleted extends FastSpringEvent
{
    public function order(): Order
    {
        return new Order($this->data);
    }
}
