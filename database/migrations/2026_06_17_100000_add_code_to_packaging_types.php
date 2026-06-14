<?php

use App\Support\WaterProductLineCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packaging_types', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->unique()->after('id');
        });

        foreach (WaterProductLineCatalog::packagingTypes() as $type) {
            DB::table('packaging_types')
                ->where('name', $type['name'])
                ->update(['code' => $type['code']]);
        }

        foreach (DB::table('packaging_types')->whereNull('code')->orderBy('id')->get() as $row) {
            DB::table('packaging_types')
                ->where('id', $row->id)
                ->update(['code' => 'PKG-' . $row->id]);
        }
    }

    public function down(): void
    {
        Schema::table('packaging_types', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
