<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTableForCashierForFastspring extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('billable_id');
            $blueprint->string('billable_type');

            $blueprint->string('fastspring_id')->unique();
            $blueprint->string('type')->nullable();
            $blueprint->string('subscription_display')->nullable();
            $blueprint->string('subscription_product')->nullable();
            $blueprint->integer('subscription_sequence')->nullable();
            $blueprint->string('invoice_url');
            $blueprint->decimal('total', 8, 2);
            $blueprint->decimal('tax', 8, 2);
            $blueprint->decimal('subtotal', 8, 2);
            $blueprint->decimal('discount', 8, 2);
            $blueprint->string('currency');
            $blueprint->string('payment_type');
            $blueprint->boolean('completed');
            $blueprint->timestamp('subscription_period_start_date')->nullable();
            $blueprint->timestamp('subscription_period_end_date')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['billable_id', 'billable_type']);
        });
    }
}
