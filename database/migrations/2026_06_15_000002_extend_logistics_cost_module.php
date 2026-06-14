<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_bill_id')->constrained('logistics_bills')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('paid_at');
            $table->string('method')->nullable();
            $table->string('batch_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('carrier_rate_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('delivery_route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->string('unit', 20)->default('trip');
            $table->decimal('rate', 14, 2);
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['supplier_id', 'delivery_route_id', 'is_active']);
        });

        Schema::create('fleet_recurring_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('expense_type', 20)->default('rent');
            $table->decimal('amount', 14, 2);
            $table->unsignedTinyInteger('day_of_month')->default(1);
            $table->string('description')->nullable();
            $table->string('payment_type', 20)->default('bank');
            $table->string('payment_account_key', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('last_generated_for')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_recurring_charges');
        Schema::dropIfExists('carrier_rate_cards');
        Schema::dropIfExists('logistics_bill_payments');
    }
};
