<?php

namespace Tests\Feature\TreatmentInfo;

use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Models\SessionCorrection;
use App\Entities\TreatmentInfo\Models\TreatmentCorrection;
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

    public function test_create_correction_persists_audit_row_and_updates_treatment(): void
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

        $svc->createCorrection($treatment->id, [
            'description' => 'Deep cleaning',
            'global_price' => '150.00',
            'reason' => 'Prix ajusté après contrôle',
        ]);

        $correction = TreatmentCorrection::query()->first();
        $this->assertNotNull($correction);
        $this->assertSame('120.00', (string) $correction->old_global_price);
        $this->assertSame('150.00', (string) $correction->new_global_price);
        $this->assertSame('Cleaning', $correction->old_description);
        $this->assertSame('Deep cleaning', $correction->new_description);
        $this->assertSame('Prix ajusté après contrôle', $correction->reason);

        $this->assertSame('150.00', (string) $treatment->fresh()->global_price);
        $this->assertSame('150.00', (string) $treatment->fresh()->remaining_amount);
        $this->assertSame('Deep cleaning', $treatment->fresh()->description);
    }

    public function test_update_session_persists_session_correction_history(): void
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
            'global_price' => '200.00',
        ]);

        $session = $svc->createSession($treatment->id, [
            'session_date' => now()->subDay(),
            'received_payment' => '80.00',
            'notes' => 'old note',
        ]);

        $svc->updateSession($session->id, [
            'session_date' => now(),
            'received_payment' => '90.00',
            'notes' => 'new note',
            'reason' => 'Correction de saisie',
        ]);

        $correction = SessionCorrection::query()->first();
        $this->assertNotNull($correction);
        $this->assertSame('80.00', (string) $correction->old_received_payment);
        $this->assertSame('90.00', (string) $correction->new_received_payment);
        $this->assertSame('old note', $correction->old_notes);
        $this->assertSame('new note', $correction->new_notes);
        $this->assertSame('Correction de saisie', $correction->reason);
    }

    public function test_cancel_session_updates_status_and_syncs_remaining_amount(): void
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

        $session = $svc->createSession($treatment->id, [
            'session_date' => now(),
            'received_payment' => '100.00',
            'notes' => 'first payment',
        ]);

        $this->assertSame('200.00', (string) $treatment->fresh()->remaining_amount);

        $svc->cancelSession($session->id);

        $session = $session->fresh();
        $this->assertSame('cancelled', $session->status);
        $this->assertNotNull($session->cancelled_at);
        $this->assertSame('300.00', (string) $treatment->fresh()->remaining_amount);
    }
}
