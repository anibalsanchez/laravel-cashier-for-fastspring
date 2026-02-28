<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Illuminate\Queue\SerializesModels;

class FastSpringEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     *
     * @return void
     */
    public function __construct(public string $id, public string $type, public bool $live, public bool $processed, public int $created, public array $data) {}

    /**
     * Get the billable entity instance
     *
     * @return \Photalika\CashierForFastspring\Billable|null
     */
    public function billable()
    {
        if (! isset($this->data['account']['id'])) {
            return null;
        }

        $fastspringId = $this->data['account']['id'];

        return \Photalika\CashierForFastspring\Cashier::findBillable($fastspringId);
    }
}
