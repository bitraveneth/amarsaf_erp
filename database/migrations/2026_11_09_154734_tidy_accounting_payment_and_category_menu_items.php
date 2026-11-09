<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_items')
            ->where('path', '/admin/payment-methods')
            ->update(['name' => 'Payment methods']);

        DB::table('menu_items')
            ->where('path', '/admin/expense-categories')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('menu_items')
            ->where('path', '/admin/payment-methods')
            ->update(['name' => 'Banks & payment methods']);

        DB::table('menu_items')
            ->where('path', '/admin/expense-categories')
            ->update(['is_active' => true]);
    }
};
