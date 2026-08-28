<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->string('kind', 20)->default('agent')->after('id');
            $table->index(['kind', 'period_start', 'period_end']);
        });

        DB::table('sales_targets')->whereNotNull('agent_id')->update(['kind' => 'agent']);
        DB::table('sales_targets')->whereNotNull('employee_id')->whereNull('agent_id')->update(['kind' => 'employee']);
        DB::table('sales_targets')->whereNull('agent_id')->whereNull('employee_id')->update(['kind' => 'company']);
    }

    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropIndex(['kind', 'period_start', 'period_end']);
            $table->dropColumn('kind');
        });
    }
};
