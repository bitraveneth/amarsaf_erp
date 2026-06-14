<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('name');
        });

        Schema::create('supplier_supplier_product_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('supplier_product_category_id');
            $table->timestamps();

            $table->foreign('supplier_product_category_id', 'ssp_category_fk')
                ->references('id')
                ->on('supplier_product_categories')
                ->cascadeOnDelete();

            $table->unique(['supplier_id', 'supplier_product_category_id'], 'ssp_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_supplier_product_category');
        Schema::dropIfExists('supplier_product_categories');
    }
};
