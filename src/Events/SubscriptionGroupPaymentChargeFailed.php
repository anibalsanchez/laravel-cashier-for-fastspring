<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Photalika\CashierForFastspring\Events\Concerns\SupportsSubscription;

class SubscriptionGroupPaymentChargeFailed extends FastSpringEvent
{
    use SupportsSubscription;
}
