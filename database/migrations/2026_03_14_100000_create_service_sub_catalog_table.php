<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot for unlimited service ↔ subcatalog bindings.
     */
    public function up(): void
    {
        if (!Schema::hasTable('service_sub_catalog')) {
            Schema::create('service_sub_catalog', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('sub_catalog_id')->constrained('sub_catalogs')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['service_id', 'sub_catalog_id']);
            });
        }

        if (Schema::hasTable('services') && Schema::hasColumn('services', 'sub_catalog_id')) {
            $now = now();
            $rows = DB::table('services')
                ->whereNotNull('sub_catalog_id')
                ->select('id as service_id', 'sub_catalog_id')
                ->get()
                ->map(fn ($row) => [
                    'service_id' => $row->service_id,
                    'sub_catalog_id' => $row->sub_catalog_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('service_sub_catalog')->insertOrIgnore($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sub_catalog');
    }
};
