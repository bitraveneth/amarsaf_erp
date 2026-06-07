<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_on_hand', 14, 4)->default(0);
            $table->decimal('total_value', 14, 2)->default(0);
            $table->decimal('avg_unit_cost', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });

        Schema::create('inventory_gl_posts', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_type', 'source_type', 'source_id'], 'inventory_gl_posts_unique_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_gl_posts');
        Schema::dropIfExists('inventory_valuations');
    }
};
