<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Photalika\CashierForFastspring\Events\Concerns\SupportsSubscription;

class SubscriptionUpdated extends FastSpringEvent
{
    use SupportsSubscription;
}
