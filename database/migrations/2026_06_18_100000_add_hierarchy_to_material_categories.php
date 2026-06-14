<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_categories', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->unique()->after('id');
            $table->foreignId('parent_id')->nullable()->after('code')->constrained('material_categories')->nullOnDelete();
            $table->boolean('is_system')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('material_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'is_system']);
        });
    }
};
