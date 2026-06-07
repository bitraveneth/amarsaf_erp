<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->unsignedInteger('qc_passed_quantity')->nullable()->after('quantity');
            $table->unsignedInteger('qc_rejected_quantity')->nullable()->after('qc_passed_quantity');
            $table->text('qc_notes')->nullable()->after('qc_rejected_quantity');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE production_runs MODIFY qc_status ENUM('pending','approved','rejected','partial') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropColumn(['qc_passed_quantity', 'qc_rejected_quantity', 'qc_notes']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE production_runs MODIFY qc_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
