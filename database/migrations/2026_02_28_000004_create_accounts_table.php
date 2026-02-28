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
        Schema::create('accounts', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('billable_id');
            $blueprint->string('billable_type');

            $blueprint->string('fastspring_id')->unique();
            $blueprint->string('company')->nullable();
            $blueprint->string('phone')->nullable();
            $blueprint->string('language')->nullable();
            $blueprint->string('country')->nullable();

            $blueprint->index(['billable_id', 'billable_type']);
        });
    }
};
