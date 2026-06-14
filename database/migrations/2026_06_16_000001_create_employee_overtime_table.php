<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_overtime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('work_date');
            $table->decimal('hours', 8, 2);
            $table->decimal('rate_multiplier', 4, 2)->default(1.5);
            $table->decimal('hourly_rate', 14, 2)->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('reason')->nullable();
            $table->string('status')->default('pending');
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('salary_distribution_id')->nullable()->constrained('salary_distributions')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'work_date']);
            $table->index(['status', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_overtime');
    }
};
