<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Concerns;

trait ManagesInvoices
{
    /**
     * Get all of the FastSpring invoices for the current entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function invoices()
    {
        return $this->morphMany(\Photalika\CashierForFastspring\Cashier::$invoiceModel, 'billable')->orderBy('created_at', 'desc');
    }
}
