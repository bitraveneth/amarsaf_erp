<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('accounts')->nullOnDelete();
            $table->boolean('is_group')->default(false)->after('type');
            $table->unsignedTinyInteger('level')->default(0)->after('is_group');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('level');
            $table->string('path', 500)->nullable()->after('sort_order');
            $table->string('report_root', 40)->nullable()->after('path');
            $table->string('slug', 100)->nullable()->unique()->after('report_root');

            $table->index(['parent_id', 'sort_order']);
            $table->index('path');
            $table->index('is_group');
            $table->index('report_root');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id', 'sort_order']);
            $table->dropIndex(['path']);
            $table->dropIndex(['is_group']);
            $table->dropIndex(['report_root']);
            $table->dropColumn([
                'parent_id',
                'is_group',
                'level',
                'sort_order',
                'path',
                'report_root',
                'slug',
            ]);
        });
    }
};
