<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Photalika\CashierForFastspring\Events\Concerns\SupportsSubscription;

class SubscriptionTrialReminder extends FastSpringEvent
{
    use SupportsSubscription;
}
