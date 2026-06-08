<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('picked_quantity', 12, 2)->default(0)->after('quantity');
            $table->decimal('packed_quantity', 12, 2)->default(0)->after('picked_quantity');
        });

        DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['picked', 'packed', 'dispatched', 'delivered'])
            ->update(['order_items.picked_quantity' => DB::raw('order_items.quantity')]);

        DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['packed', 'dispatched', 'delivered'])
            ->update(['order_items.packed_quantity' => DB::raw('order_items.quantity')]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['picked_quantity', 'packed_quantity']);
        });
    }
};
