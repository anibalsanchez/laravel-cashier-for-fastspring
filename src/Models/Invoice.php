<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * This class describes an invoice.
 *
 * {@inheritdoc}
 */
class Invoice extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subscription_period_start_date' => 'datetime',
            'subscription_period_end_date' => 'datetime',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the billable model related to the invoice.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
