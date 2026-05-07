<?php

namespace Tests\Feature\TreatmentInfo;

use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentLineSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_line_computes_line_total(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'A',
            'last_name' => 'B',
            'telephone' => '0611111111',
            'notes' => null,
        ]);

        $svc = app(TreatmentInfoServiceInterface::class);
        $line = $svc->create($patient->id, [
            'description' => 'Cleaning',
            'quantity' => 2,
            'unit_price' => '25.50',
        ]);

        $this->assertSame('51.00', (string) $line->line_total);
        $this->assertSame(2, $line->quantity);
    }
}
