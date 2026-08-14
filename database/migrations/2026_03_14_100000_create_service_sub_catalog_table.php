<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unlimited service ↔ subcatalog bindings via pivot.
     * Compatible with DBs that already dropped services.sub_catalog_id.
     */
    public function up(): void
    {
        if (!Schema::hasTable('service_sub_catalog')) {
            Schema::create('service_sub_catalog', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('sub_catalog_id')->constrained('sub_catalogs')->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->unique(['service_id', 'sub_catalog_id']);
            });
        } elseif (!Schema::hasColumn('service_sub_catalog', 'is_primary')) {
            Schema::table('service_sub_catalog', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('sub_catalog_id');
            });
        }

        // Backfill from legacy FK if it still exists
        if (Schema::hasTable('services') && Schema::hasColumn('services', 'sub_catalog_id')) {
            $now = now();
            $rows = DB::table('services')
                ->whereNotNull('sub_catalog_id')
                ->select('id as service_id', 'sub_catalog_id')
                ->get()
                ->map(fn ($row) => [
                    'service_id' => $row->service_id,
                    'sub_catalog_id' => $row->sub_catalog_id,
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('service_sub_catalog')->insertOrIgnore($chunk);
            }
        }

        // Ensure every service with links has exactly one primary
        $serviceIds = DB::table('service_sub_catalog')->distinct()->pluck('service_id');
        foreach ($serviceIds as $serviceId) {
            $hasPrimary = DB::table('service_sub_catalog')
                ->where('service_id', $serviceId)
                ->where('is_primary', true)
                ->exists();

            if (!$hasPrimary) {
                $firstId = DB::table('service_sub_catalog')
                    ->where('service_id', $serviceId)
                    ->orderBy('id')
                    ->value('id');

                if ($firstId) {
                    DB::table('service_sub_catalog')
                        ->where('id', $firstId)
                        ->update(['is_primary' => true]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_sub_catalog') && Schema::hasColumn('service_sub_catalog', 'is_primary')) {
            Schema::table('service_sub_catalog', function (Blueprint $table) {
                $table->dropColumn('is_primary');
            });
        }
    }
};
