<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_sub_catalog') && Schema::hasColumn('service_sub_catalog', 'is_primary')) {
            Schema::table('service_sub_catalog', function (Blueprint $table) {
                $table->dropColumn('is_primary');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_sub_catalog') && !Schema::hasColumn('service_sub_catalog', 'is_primary')) {
            Schema::table('service_sub_catalog', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('sub_catalog_id');
            });
        }
    }
};
