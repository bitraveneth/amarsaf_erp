<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('payable_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('default_payment_type', 20)->default('bank');
            $table->string('default_payment_account_key', 100)->default('bank_default');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('date')->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->after('expense_category_id')->constrained('accounts')->nullOnDelete();
            $table->string('payment_type', 20)->default('bank')->after('amount');
            $table->string('payment_account_key', 100)->default('bank_default')->after('payment_type');
            $table->string('analytic_label')->nullable()->after('payment_account_key');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('income_account_id')->nullable()->after('tax_class_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('expense_account_id')->nullable()->after('income_account_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('inventory_account_id')->nullable()->after('expense_account_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('material_categories', function (Blueprint $table) {
            $table->foreignId('income_account_id')->nullable()->after('description')->constrained('accounts')->nullOnDelete();
            $table->foreignId('expense_account_id')->nullable()->after('income_account_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('inventory_account_id')->nullable()->after('expense_account_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->string('analytic_label')->nullable()->after('description');
        });

        Schema::table('salary_distributions', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('employee_id')->constrained('journal_entries')->nullOnDelete();
            $table->string('payment_type', 20)->default('bank')->after('payment_method');
            $table->string('payment_account_key', 100)->default('bank_default')->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('salary_distributions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('journal_entry_id');
            $table->dropColumn(['payment_type', 'payment_account_key']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropColumn('analytic_label');
        });

        Schema::table('material_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('income_account_id');
            $table->dropConstrainedForeignId('expense_account_id');
            $table->dropConstrainedForeignId('inventory_account_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('income_account_id');
            $table->dropConstrainedForeignId('expense_account_id');
            $table->dropConstrainedForeignId('inventory_account_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_category_id');
            $table->dropConstrainedForeignId('account_id');
            $table->dropColumn(['payment_type', 'payment_account_key', 'analytic_label']);
        });

        Schema::dropIfExists('expense_categories');
    }
};
