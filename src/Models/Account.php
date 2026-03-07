<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This class describes an Account.
 *
 * {@inheritdoc}
 */
class Account extends Model
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the billable model related to the account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function billable()
    {
        return $this->morphTo();
    }
}
