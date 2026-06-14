<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('frequency', ['per_order', 'monthly'])->default('monthly');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_commission_rules');
    }
};
