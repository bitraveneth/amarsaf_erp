<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand', 80)->nullable()->after('name');
            $table->unsignedInteger('weight_g')->nullable()->after('volume_ml');
            $table->unsignedSmallInteger('shelf_life_months')->nullable()->after('weight_g');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand', 'weight_g', 'shelf_life_months']);
        });
    }
};
