<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Photalika\CashierForFastspring\Events\Concerns\SupportsOrder;

class OrderCompleted extends FastSpringEvent
{
    use SupportsOrder;
}
