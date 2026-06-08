<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('warehouse_approved_at')->nullable()->after('submitted_at');
            $table->foreignId('warehouse_approved_by')->nullable()->after('warehouse_approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('procurement_approved_at')->nullable()->after('warehouse_approved_by');
            $table->foreignId('procurement_approved_by')->nullable()->after('procurement_approved_at')->constrained('users')->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN status ENUM('draft', 'pending_approval', 'posted', 'cancelled') NOT NULL DEFAULT 'pending_approval'");
        }
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_approved_by');
            $table->dropConstrainedForeignId('procurement_approved_by');
            $table->dropColumn([
                'submitted_at',
                'warehouse_approved_at',
                'procurement_approved_at',
            ]);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN status ENUM('draft', 'posted', 'cancelled') NOT NULL DEFAULT 'posted'");
        }
    }
};
