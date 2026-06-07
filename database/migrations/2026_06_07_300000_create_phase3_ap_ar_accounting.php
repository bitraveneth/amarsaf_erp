<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->string('batch_reference')->nullable()->after('method');
            $table->boolean('reconciled')->default(false)->after('batch_reference');
        });
    }

    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropColumn(['batch_reference', 'reconciled']);
        });
    }
};
