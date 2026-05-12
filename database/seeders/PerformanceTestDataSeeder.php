<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Bulk synthetic data for load / UI performance testing.
 *
 * Run explicitly (can take several minutes on PostgreSQL):
 *   php artisan db:seed --class=PerformanceTestDataSeeder
 *
 * Uses explicit primary keys, then resyncs PostgreSQL sequences / SQLite
 * sqlite_sequence / MySQL AUTO_INCREMENT.
 * Prefer a disposable database or backup first — this appends rows.
 */
class PerformanceTestDataSeeder extends Seeder
{
    public const PATIENTS = 5000;

    public const APPOINTMENTS = 200000;

    public const TREATMENTS = 10000;

    public const SESSIONS = 30000;

    private const CHUNK = 2000;

    public function run(): void
    {
        if (self::SESSIONS < self::TREATMENTS) {
            throw new \InvalidArgumentException('SESSIONS must be >= TREATMENTS so each treatment can have at least one session.');
        }

        $driver = DB::getDriverName();
        $now = Carbon::now();

        DB::disableQueryLog();

        $patientStart = (int) DB::table('patients')->max('id') + 1;
        $treatmentStart = (int) DB::table('treatment_infos')->max('id') + 1;
        $sessionStart = (int) DB::table('treatment_sessions')->max('id') + 1;
        $appointmentStart = (int) DB::table('appointments')->max('id') + 1;

        $this->command?->info('Seeding '.self::PATIENTS.' patients…');
        $this->seedPatients($patientStart, $now);

        $this->command?->info('Seeding '.self::TREATMENTS.' treatment_infos…');
        $this->seedTreatments($patientStart, $treatmentStart, $now);

        $this->command?->info('Seeding '.self::SESSIONS.' treatment_sessions…');
        $this->seedSessions($treatmentStart, $sessionStart, $now);

        $this->command?->info('Seeding '.self::APPOINTMENTS.' appointments…');
        $this->seedAppointments($patientStart, $appointmentStart, $now);

        if ($driver === 'pgsql') {
            $this->resyncPostgresSequences();
        } elseif ($driver === 'sqlite') {
            $this->resyncSqliteSequences();
        } elseif ($driver === 'mysql') {
            $this->resyncMysqlAutoIncrements();
        }

        $this->command?->info('Performance test data seed complete.');
    }

    private function seedPatients(int $startId, Carbon $now): void
    {
        $chunk = [];
        for ($i = 0; $i < self::PATIENTS; $i++) {
            $id = $startId + $i;
            $chunk[] = [
                'id' => $id,
                'first_name' => 'Perf',
                'last_name' => 'P'.$id,
                'telephone' => 'P'.str_pad((string) $id, 15, '0', STR_PAD_LEFT),
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
            if (count($chunk) >= self::CHUNK) {
                DB::table('patients')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk !== []) {
            DB::table('patients')->insert($chunk);
        }
    }

    private function seedTreatments(int $patientStart, int $treatmentStart, Carbon $now): void
    {
        $chunk = [];
        for ($t = 0; $t < self::TREATMENTS; $t++) {
            $id = $treatmentStart + $t;
            $patientId = $patientStart + ($t % self::PATIENTS);
            $chunk[] = [
                'id' => $id,
                'patient_id' => $patientId,
                'description' => 'Perf treatment '.$id,
                'global_price' => 1000,
                'remaining_amount' => 0,
                'status' => 'paid',
                'cancelled_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($chunk) >= self::CHUNK) {
                DB::table('treatment_infos')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk !== []) {
            DB::table('treatment_infos')->insert($chunk);
        }
    }

    private function seedSessions(int $treatmentStart, int $sessionStart, Carbon $now): void
    {
        $sessionsPerTreatment = intdiv(self::SESSIONS, self::TREATMENTS);
        $remainder = self::SESSIONS % self::TREATMENTS;

        $chunk = [];
        $sid = $sessionStart;
        for ($t = 0; $t < self::TREATMENTS; $t++) {
            $treatmentId = $treatmentStart + $t;
            $count = $sessionsPerTreatment + ($t < $remainder ? 1 : 0);
            for ($s = 0; $s < $count; $s++) {
                $chunk[] = [
                    'id' => $sid,
                    'treatment_info_id' => $treatmentId,
                    'session_date' => Carbon::parse($now)->subDays(($sid - $sessionStart) % 500)->subHours($s % 23),
                    'received_payment' => 100,
                    'notes' => null,
                    'status' => 'active',
                    'cancelled_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $sid++;
                if (count($chunk) >= self::CHUNK) {
                    DB::table('treatment_sessions')->insert($chunk);
                    $chunk = [];
                }
            }
        }
        if ($chunk !== []) {
            DB::table('treatment_sessions')->insert($chunk);
        }
    }

    private function seedAppointments(int $patientStart, int $appointmentStart, Carbon $now): void
    {
        $statuses = ['done', 'done', 'done', 'waiting', 'in_progress', 'cancelled'];
        $chunk = [];
        for ($a = 0; $a < self::APPOINTMENTS; $a++) {
            $id = $appointmentStart + $a;
            $patientId = $patientStart + ($a % self::PATIENTS);
            $status = $statuses[$a % count($statuses)];
            $started = in_array($status, ['in_progress', 'done', 'cancelled'], true) ? $now->copy()->subMinutes($a % 10000) : null;
            $completed = $status === 'done' ? $now->copy()->subMinutes($a % 5000) : null;
            $chunk[] = [
                'id' => $id,
                'patient_id' => $patientId,
                'status' => $status,
                'started_at' => $started,
                'completed_at' => $completed,
                'created_at' => $now->copy()->subDays($a % 730)->subMinutes($a % 1440),
                'updated_at' => $now,
            ];
            if (count($chunk) >= self::CHUNK) {
                DB::table('appointments')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk !== []) {
            DB::table('appointments')->insert($chunk);
        }
    }

    private function resyncPostgresSequences(): void
    {
        foreach (['patients', 'appointments', 'treatment_infos', 'treatment_sessions'] as $table) {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM \"{$table}\"), 1))"
            );
        }
    }

    private function resyncSqliteSequences(): void
    {
        foreach (['patients', 'appointments', 'treatment_infos', 'treatment_sessions'] as $table) {
            $max = (int) DB::table($table)->max('id');
            if ($max < 1) {
                continue;
            }
            $exists = DB::table('sqlite_sequence')->where('name', $table)->exists();
            if ($exists) {
                DB::table('sqlite_sequence')->where('name', $table)->update(['seq' => $max]);
            } else {
                DB::table('sqlite_sequence')->insert(['name' => $table, 'seq' => $max]);
            }
        }
    }

    private function resyncMysqlAutoIncrements(): void
    {
        foreach (['patients', 'appointments', 'treatment_infos', 'treatment_sessions'] as $table) {
            $max = (int) DB::table($table)->max('id');
            if ($max < 1) {
                continue;
            }
            $next = $max + 1;
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
        }
    }
}
