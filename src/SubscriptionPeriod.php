<?php

namespace Photalika\CashierForFastspring;

use Illuminate\Database\Eloquent\Model;

/**
 * This class describes a subscription period.
 *
 * {@inheritdoc}
 */
class SubscriptionPeriod extends Model
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
            'start_date' => 'date',
            'end_date' => 'date',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the subscription.
     *
     * @return object Subscription object.
     */
    public function subscription()
    {
        return $this->belongsTo(\Photalika\CashierForFastspring\Subscription::class);
    }

    public function getCarbonStartDate(): void {}
}
