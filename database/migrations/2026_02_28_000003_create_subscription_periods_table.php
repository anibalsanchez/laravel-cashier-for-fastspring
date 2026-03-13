<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_periods', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('subscription_id');

            $blueprint->string('type');
            $blueprint->timestamp('start_date')->nullable();
            $blueprint->timestamp('end_date')->nullable();
            $blueprint->timestamps();

            $blueprint->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');
            $blueprint->unique([
                'subscription_id',
                'type',
                'start_date',
                'end_date',
            ], 'subscription_period_unique');
        });
    }
};
