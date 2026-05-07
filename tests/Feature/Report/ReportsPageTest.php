<?php

namespace Tests\Feature\Report;

use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_loads(): void
    {
        $this->get('/reports')
            ->assertOk()
            ->assertSee(__('Reports'))
            ->assertSee(__('Revenue Details'))
            ->assertSee(__('Patient Credits'));
    }

    public function test_reports_page_renders_revenue_and_credits_data(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-07 10:00:00'));

        $patient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611000100',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Implant',
            'global_price' => 1000,
            'remaining_amount' => 250,
        ]);

        DB::table('treatment_sessions')->insert([
            'treatment_info_id' => $treatment->id,
            'session_date' => Carbon::parse('2026-05-07 09:30:00'),
            'received_payment' => 750,
            'notes' => null,
            'created_at' => Carbon::parse('2026-05-07 09:30:00'),
            'updated_at' => Carbon::parse('2026-05-07 09:30:00'),
        ]);

        $this->get('/reports')
            ->assertOk()
            ->assertSee('750.00')
            ->assertSee('250.00')
            ->assertSee('Nadia Karim')
            ->assertSee('/patients/'.$patient->id.'/treatments');

        Carbon::setTestNow();
    }
}
