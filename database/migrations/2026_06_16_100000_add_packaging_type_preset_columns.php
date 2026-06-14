<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packaging_types', function (Blueprint $table) {
            $table->unsignedInteger('units_per_pack')->nullable()->after('description');
            $table->string('size_key', 32)->nullable()->after('units_per_pack');
            $table->boolean('is_system')->default(false)->after('size_key');
        });
    }

    public function down(): void
    {
        Schema::table('packaging_types', function (Blueprint $table) {
            $table->dropColumn(['units_per_pack', 'size_key', 'is_system']);
        });
    }
};
