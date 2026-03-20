<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Models;

class SessionItem
{
    /**
     * The product of item.
     *
     * @var string
     */
    public $product;

    /**
     * The quantity of item.
     *
     * @var int
     */
    public $quantity;

    /**
     * Create a new item instance.
     *
     * @param  object  $item  Item of session
     * @return void
     */
    public function __construct($item)
    {
        $this->product = $item->product;
        $this->quantity = $item->quantity;
    }
}
