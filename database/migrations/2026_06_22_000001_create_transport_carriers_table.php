<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
        });

        Schema::table('logistics_bills', function (Blueprint $table) {
            $table->foreignId('transport_carrier_id')->nullable()->after('id')->constrained('transport_carriers')->restrictOnDelete();
        });

        Schema::table('carrier_rate_cards', function (Blueprint $table) {
            $table->foreignId('transport_carrier_id')->nullable()->after('id')->constrained('transport_carriers')->restrictOnDelete();
        });

        $supplierIds = collect()
            ->merge(DB::table('logistics_bills')->pluck('supplier_id'))
            ->merge(DB::table('carrier_rate_cards')->pluck('supplier_id'))
            ->merge(DB::table('suppliers')->where('is_transport_carrier', true)->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        $map = [];

        foreach ($supplierIds as $supplierId) {
            $supplier = DB::table('suppliers')->where('id', $supplierId)->first();

            if (! $supplier) {
                continue;
            }

            $carrierId = DB::table('transport_carriers')->insertGetId([
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'tax_id' => $supplier->tax_id,
                'notes' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $map[(int) $supplierId] = (int) $carrierId;
        }

        foreach ($map as $supplierId => $carrierId) {
            DB::table('logistics_bills')->where('supplier_id', $supplierId)->update(['transport_carrier_id' => $carrierId]);
            DB::table('carrier_rate_cards')->where('supplier_id', $supplierId)->update(['transport_carrier_id' => $carrierId]);
        }

        $remainingSupplierIds = DB::table('logistics_bills')
            ->whereNull('transport_carrier_id')
            ->whereNotNull('supplier_id')
            ->pluck('supplier_id')
            ->merge(
                DB::table('carrier_rate_cards')
                    ->whereNull('transport_carrier_id')
                    ->whereNotNull('supplier_id')
                    ->pluck('supplier_id')
            )
            ->unique();

        foreach ($remainingSupplierIds as $supplierId) {
            if (isset($map[$supplierId])) {
                continue;
            }

            $supplier = DB::table('suppliers')->where('id', $supplierId)->first();

            if (! $supplier) {
                continue;
            }

            $carrierId = DB::table('transport_carriers')->insertGetId([
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'tax_id' => $supplier->tax_id,
                'notes' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $map[(int) $supplierId] = (int) $carrierId;

            DB::table('logistics_bills')->where('supplier_id', $supplierId)->update(['transport_carrier_id' => $carrierId]);
            DB::table('carrier_rate_cards')->where('supplier_id', $supplierId)->update(['transport_carrier_id' => $carrierId]);
        }

        Schema::table('logistics_bills', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::table('carrier_rate_cards', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('is_transport_carrier');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('is_transport_carrier')->default(false)->after('is_one_time');
        });

        Schema::table('logistics_bills', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained('suppliers')->restrictOnDelete();
        });

        Schema::table('carrier_rate_cards', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained('suppliers')->cascadeOnDelete();
        });

        Schema::table('logistics_bills', function (Blueprint $table) {
            $table->dropForeign(['transport_carrier_id']);
            $table->dropColumn('transport_carrier_id');
        });

        Schema::table('carrier_rate_cards', function (Blueprint $table) {
            $table->dropForeign(['transport_carrier_id']);
            $table->dropColumn('transport_carrier_id');
        });

        Schema::dropIfExists('transport_carriers');
    }
};
