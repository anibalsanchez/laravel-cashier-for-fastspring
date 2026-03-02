<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events\Concerns;

use Photalika\CashierForFastspring\Events\Models\Subscription;

trait SupportsSubscription
{
    public function subscription(): Subscription
    {
        return new Subscription($this->data);
    }
}
