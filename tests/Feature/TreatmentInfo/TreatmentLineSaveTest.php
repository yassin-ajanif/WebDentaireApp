<?php

namespace Tests\Feature\TreatmentInfo;

use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentLineSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_treatment_initializes_remaining_amount_to_global_price(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'A',
            'last_name' => 'B',
            'telephone' => '0611111111',
            'notes' => null,
        ]);

        $svc = app(TreatmentInfoServiceInterface::class);
        $treatment = $svc->createTreatment($patient->id, [
            'description' => 'Cleaning',
            'global_price' => '120.00',
        ]);

        $this->assertSame('120.00', (string) $treatment->global_price);
        $this->assertSame('120.00', (string) $treatment->remaining_amount);
    }

    public function test_create_session_updates_remaining_amount(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'A',
            'last_name' => 'B',
            'telephone' => '0611111111',
            'notes' => null,
        ]);

        $svc = app(TreatmentInfoServiceInterface::class);
        $treatment = $svc->createTreatment($patient->id, [
            'description' => 'Whitening',
            'global_price' => '300.00',
        ]);

        $svc->createSession($treatment->id, [
            'session_date' => now(),
            'received_payment' => '100.00',
            'notes' => 'first payment',
        ]);

        $this->assertSame('200.00', (string) $treatment->fresh()->remaining_amount);
    }

    public function test_create_session_rejects_overpayment(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'A',
            'last_name' => 'B',
            'telephone' => '0611111111',
            'notes' => null,
        ]);

        $svc = app(TreatmentInfoServiceInterface::class);
        $treatment = $svc->createTreatment($patient->id, [
            'description' => 'Root canal',
            'global_price' => '100.00',
        ]);

        $svc->createSession($treatment->id, [
            'session_date' => now(),
            'received_payment' => '70.00',
            'notes' => 'first',
        ]);

        $this->expectException(\DomainException::class);

        $svc->createSession($treatment->id, [
            'session_date' => now(),
            'received_payment' => '40.00',
            'notes' => 'second',
        ]);
    }
}
