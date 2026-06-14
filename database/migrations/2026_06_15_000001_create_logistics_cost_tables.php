<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('ownership', 20)->default('own')->after('type');
            $table->string('fuel_type', 20)->nullable()->after('ownership');
            $table->unsignedInteger('odometer_km')->nullable()->after('fuel_type');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('is_transport_carrier')->default(false)->after('is_one_time');
        });

        Schema::create('fleet_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('expense_type', 20);
            $table->date('expense_date');
            $table->decimal('amount', 14, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('status', 20)->default('recorded');
            $table->string('payment_type', 20)->default('bank');
            $table->string('payment_account_key', 64)->nullable();
            $table->decimal('fuel_litres', 10, 2)->nullable();
            $table->decimal('fuel_rate', 10, 2)->nullable();
            $table->unsignedInteger('odometer_km')->nullable();
            $table->foreignId('delivery_route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->date('trip_date')->nullable();
            $table->string('analytic_label')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'expense_date']);
            $table->index('expense_type');
        });

        Schema::create('logistics_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('number')->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->string('service_type', 30)->default('external_freight');
            $table->foreignId('delivery_route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->date('trip_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('net_total', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->index(['supplier_id', 'bill_date']);
        });

        Schema::create('logistics_bill_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_bill_id')->constrained('logistics_bills')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_bill_lines');
        Schema::dropIfExists('logistics_bills');
        Schema::dropIfExists('fleet_expenses');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('is_transport_carrier');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['ownership', 'fuel_type', 'odometer_km']);
        });
    }
};
