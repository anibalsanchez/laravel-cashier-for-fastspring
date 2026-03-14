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
        Schema::create('subscriptions', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('billable_id');
            $blueprint->string('billable_type');

            $blueprint->string('name');
            $blueprint->string('fastspring_id')->nullable();
            $blueprint->string('plan');
            $blueprint->string('state');
            $blueprint->integer('quantity');
            $blueprint->string('currency');
            $blueprint->string('interval_unit');
            $blueprint->integer('interval_length');
            $blueprint->string('swap_to')->nullable();
            $blueprint->timestamp('swap_at')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['billable_id', 'billable_type']);
            $blueprint->index(['fastspring_id']);
        });
    }
};
