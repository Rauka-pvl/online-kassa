<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('manage_token', 64)->nullable()->unique();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 20)->nullable();
        });

        DB::table('appointments')->whereNull('manage_token')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('appointments')->where('id', $row->id)->update([
                    'manage_token' => Str::random(48),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique(['manage_token']);
            $table->dropColumn(['manage_token', 'cancelled_at', 'cancelled_by']);
        });
    }
};
