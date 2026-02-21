<?php

namespace Photalika\CashierForFastspring;

use Illuminate\Database\Eloquent\Model;

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
            'subscription_period_start_date' => 'date',
            'subscription_period_end_date' => 'date',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the invoice.
     *
     * @return self
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $model = getenv('FASTSPRING_MODEL') ?: config('services.fastspring.model', 'App\\User');

        $model = new $model;

        return $this->belongsTo($model::class, $model->getForeignKey());
    }
}
