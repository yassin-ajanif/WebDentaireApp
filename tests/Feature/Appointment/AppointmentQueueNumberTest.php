<?php

namespace Tests\Feature\Appointment;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentQueueNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_queue_orders_by_created_at_then_id(): void
    {
        $older = Appointment::query()->create([
            'patient_id' => null,
            'status' => AppointmentStatus::Waiting,
        ]);
        $older->forceFill([
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ])->saveQuietly();

        $newer = Appointment::query()->create([
            'patient_id' => null,
            'status' => AppointmentStatus::Waiting,
        ]);

        $list = app(AppointmentServiceInterface::class)->listQueue(null);

        $this->assertSame([$older->id, $newer->id], $list->pluck('id')->all());
    }
}
