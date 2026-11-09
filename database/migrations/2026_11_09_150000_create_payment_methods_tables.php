<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 100);
            $table->string('kind', 20); // bank | mobile | cash
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payment_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_category_id')->constrained('payment_method_categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->string('account_number', 80)->nullable();
            $table->string('branch', 120)->nullable();
            $table->string('routing', 80)->nullable();
            $table->string('details', 255)->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('payment_instrument_id')
                    ->nullable()
                    ->after('payment_account_key')
                    ->constrained('payment_instruments')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('fleet_expenses')) {
            Schema::table('fleet_expenses', function (Blueprint $table) {
                $table->foreignId('payment_instrument_id')
                    ->nullable()
                    ->after('payment_account_key')
                    ->constrained('payment_instruments')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fleet_expenses') && Schema::hasColumn('fleet_expenses', 'payment_instrument_id')) {
            Schema::table('fleet_expenses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('payment_instrument_id');
            });
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'payment_instrument_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('payment_instrument_id');
            });
        }

        Schema::dropIfExists('payment_instruments');
        Schema::dropIfExists('payment_method_categories');
    }
};
