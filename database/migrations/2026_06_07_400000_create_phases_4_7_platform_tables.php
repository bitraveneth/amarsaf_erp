<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'reorder_level')) {
                $table->unsignedInteger('reorder_level')->nullable()->after('standard_cost');
            }
        });

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'reorder_level')) {
                $table->dropColumn('reorder_level');
            }
        });
    }
};
