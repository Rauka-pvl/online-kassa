<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeConsultationsCommand extends Command
{
    protected $signature = 'services:merge-consultations {--dry-run : Show changes without writing}';

    protected $description = 'Merge duplicate consultation services into one canonical row per exact name';

    /** @var list<string> */
    private array $canonicalNames = [
        'Консультативный первичный прием врача',
        'Консультативный повторный прием врача (в течении 14 дней)',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry-run: no changes will be written.');
        }

        foreach ($this->canonicalNames as $name) {
            $this->mergeNameGroup($name, $dryRun);
        }

        $this->info($dryRun ? 'Dry-run finished.' : 'Merge finished.');

        return self::SUCCESS;
    }

    private function mergeNameGroup(string $name, bool $dryRun): void
    {
        $services = Service::query()
            ->where('name', $name)
            ->orderBy('id')
            ->get();

        if ($services->count() <= 1) {
            $this->line("«{$name}»: nothing to merge ({$services->count()} row).");

            return;
        }

        $canonical = $services->first();
        $duplicates = $services->slice(1)->filter(function (Service $dup) {
            if ($dup->is_active) {
                return true;
            }

            return Appointment::where('service_id', $dup->id)->exists()
                || DB::table('schedule_services')->where('service_id', $dup->id)->exists()
                || DB::table('service_sub_catalog')->where('service_id', $dup->id)->exists();
        })->values();

        if ($duplicates->isEmpty()) {
            $this->line("«{$name}»: already clean (canonical #{$canonical->id}).");

            return;
        }

        $this->info("«{$name}»: keep #{$canonical->id}, merge " . $duplicates->pluck('id')->implode(', '));

        if ($dryRun) {
            foreach ($duplicates as $dup) {
                $appts = Appointment::where('service_id', $dup->id)->count();
                $schedules = DB::table('schedule_services')->where('service_id', $dup->id)->count();
                $subs = DB::table('service_sub_catalog')->where('service_id', $dup->id)->count();
                $this->line("  #{$dup->id}: appointments={$appts}, schedules={$schedules}, subcatalogs={$subs}");
            }

            return;
        }

        DB::transaction(function () use ($canonical, $duplicates) {
            foreach ($duplicates as $dup) {
                Appointment::where('service_id', $dup->id)->update(['service_id' => $canonical->id]);

                $scheduleIds = DB::table('schedule_services')
                    ->where('service_id', $dup->id)
                    ->pluck('schedule_id');

                foreach ($scheduleIds as $scheduleId) {
                    $exists = DB::table('schedule_services')
                        ->where('schedule_id', $scheduleId)
                        ->where('service_id', $canonical->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('schedule_services')->insert([
                            'schedule_id' => $scheduleId,
                            'service_id' => $canonical->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::table('schedule_services')->where('service_id', $dup->id)->delete();

                $subIds = DB::table('service_sub_catalog')
                    ->where('service_id', $dup->id)
                    ->pluck('sub_catalog_id');

                foreach ($subIds as $subId) {
                    $exists = DB::table('service_sub_catalog')
                        ->where('service_id', $canonical->id)
                        ->where('sub_catalog_id', $subId)
                        ->exists();

                    if (!$exists) {
                        DB::table('service_sub_catalog')->insert([
                            'service_id' => $canonical->id,
                            'sub_catalog_id' => $subId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::table('service_sub_catalog')->where('service_id', $dup->id)->delete();

                $dup->update(['is_active' => false]);
            }

            if (Schema::hasColumn('services', 'sub_catalog_id')) {
                $firstSub = DB::table('service_sub_catalog')
                    ->where('service_id', $canonical->id)
                    ->orderBy('id')
                    ->value('sub_catalog_id');

                if ($firstSub) {
                    $canonical->forceFill(['sub_catalog_id' => $firstSub])->save();
                }
            }

            $canonical->update(['is_active' => true]);
        });
    }
}
