<?php

namespace Tests\Feature\Patient;

use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\Patient\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PatientTelephoneUniqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_telephone_on_second_create_throws_validation_exception(): void
    {
        Patient::query()->create([
            'first_name' => 'A',
            'last_name' => 'B',
            'telephone' => '0612345678',
            'notes' => null,
        ]);

        $svc = app(PatientServiceInterface::class);

        $this->expectException(ValidationException::class);
        $svc->create([
            'first_name' => 'C',
            'last_name' => 'D',
            'telephone' => '0612345678',
            'notes' => null,
        ]);
    }
}
