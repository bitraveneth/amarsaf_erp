<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('chemical_name', 255)->nullable()->after('supplier_name');
            $table->string('sourcing', 20)->default('purchased')->after('chemical_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['chemical_name', 'sourcing']);
        });
    }
};
