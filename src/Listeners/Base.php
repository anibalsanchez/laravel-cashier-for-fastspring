<?php

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
     * @return \Photalika\CashierForFastspring\Billable
     */
    public function getUserByFastspringId($fastspringId)
    {
        $model = getenv('FASTSPRING_MODEL') ?: config('services.fastspring.model');

        return (new $model)->where('fastspring_id', $fastspringId)->first();
    }
}
