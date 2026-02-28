<?php

declare(strict_types=1);

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
            'start_date' => 'datetime',
            'end_date' => 'datetime',

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
        return $this->belongsTo(\Photalika\CashierForFastspring\Cashier::$subscriptionModel);
    }

    public function getCarbonStartDate(): void {}
}
