<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_instruments', function (Blueprint $table) {
            $table->string('account_holder_name', 150)->nullable()->after('name');
            $table->string('bank_name', 150)->nullable()->after('account_holder_name');
            $table->string('swift_code', 40)->nullable()->after('routing');
            $table->string('iban', 80)->nullable()->after('swift_code');
            $table->string('address', 255)->nullable()->after('iban');
            $table->string('phone', 40)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('payment_instruments', function (Blueprint $table) {
            $table->dropColumn([
                'account_holder_name',
                'bank_name',
                'swift_code',
                'iban',
                'address',
                'phone',
            ]);
        });
    }
};
