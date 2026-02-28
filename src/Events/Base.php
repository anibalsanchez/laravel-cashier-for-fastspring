<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Events;

use Illuminate\Queue\SerializesModels;

class Base
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     *
     * @return void
     */
    public function __construct(public string $id, public string $type, public bool $live, public bool $processed, public int $created, public array $data) {}
}
