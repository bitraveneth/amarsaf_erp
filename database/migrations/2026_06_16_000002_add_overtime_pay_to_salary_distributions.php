<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_distributions', function (Blueprint $table) {
            $table->decimal('overtime_pay', 14, 2)->default(0)->after('commission');
        });
    }

    public function down(): void
    {
        Schema::table('salary_distributions', function (Blueprint $table) {
            $table->dropColumn('overtime_pay');
        });
    }
};
