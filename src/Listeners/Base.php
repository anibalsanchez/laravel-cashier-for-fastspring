<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Listeners;

/**
 * This class describes a base.
 */
class Base
{
    /**
     * Get the billable entity instance by Fastspring ID.
     *
     * @param  string  $fastspringId
     * @return \Photalika\CashierForFastspring\Billable|null
     */
    public function getBillableByFastspringId($fastspringId)
    {
        return \Photalika\CashierForFastspring\Cashier::findBillable($fastspringId);
    }
}
