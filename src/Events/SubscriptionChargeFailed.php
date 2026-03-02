<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Photalika\CashierForFastspring\Events\Concerns\SupportsSubscription;

class SubscriptionChargeFailed extends FastSpringEvent
{
    use SupportsSubscription;
}
