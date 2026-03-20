<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Models;

class Session
{
    /**
     * The id of session.
     *
     * @var string
     */
    public $id;

    /**
     * The currency of session.
     *
     * @var string
     */
    public $currency;

    /**
     * The expires of session.
     *
     * @var int
     */
    public $expires;

    /**
     * The order of session.
     *
     * @var \stdClass|null
     */
    public $order;

    /**
     * The account of session.
     *
     * @var string
     */
    public $account;

    /**
     * The subtotal of session.
     *
     * @var float
     */
    public $subtotal;

    /**
     * The items of session.
     *
     * @var array
     */
    public $items;

    /**
     * Create a new session instance.
     *
     * @param  object  $session  Session data from fastspring
     * @return void
     */
    public function __construct($session)
    {
        $this->id = $session->id;
        $this->currency = $session->currency;
        $this->expires = $session->expires;
        $this->order = $session->order;
        $this->account = $session->account;
        $this->subtotal = $session->subtotal;
        $this->items = array_map(fn ($item): \Photalika\CashierForFastspring\Models\SessionItem => new SessionItem($item), $session->items);
    }
}
