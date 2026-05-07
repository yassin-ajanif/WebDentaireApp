<?php

namespace Tests\Feature\Appointment;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use App\Entities\Appointment\Enums\AppointmentStatus;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiting_to_in_progress_to_done(): void
    {
        $svc = app(AppointmentServiceInterface::class);
        $a = $svc->createTicket(null);
        $this->assertSame(AppointmentStatus::Waiting, $a->status);

        $a = $svc->transitionStatus($a->id, AppointmentStatus::InProgress);
        $this->assertSame(AppointmentStatus::InProgress, $a->status);

        $a = $svc->transitionStatus($a->id, AppointmentStatus::Done);
        $this->assertSame(AppointmentStatus::Done, $a->status);
    }

    public function test_done_to_in_progress_is_allowed_for_corrections(): void
    {
        $svc = app(AppointmentServiceInterface::class);
        $a = $svc->createTicket(null);
        $svc->transitionStatus($a->id, AppointmentStatus::InProgress);
        $svc->transitionStatus($a->id, AppointmentStatus::Done);

        $a = $svc->transitionStatus($a->id, AppointmentStatus::InProgress);
        $this->assertSame(AppointmentStatus::InProgress, $a->status);
        $this->assertNotNull($a->started_at);
        $this->assertNull($a->completed_at);
    }

    public function test_waiting_cannot_transition_to_done(): void
    {
        $svc = app(AppointmentServiceInterface::class);
        $a = $svc->createTicket(null);

        $this->expectException(DomainException::class);
        $svc->transitionStatus($a->id, AppointmentStatus::Done);
    }

    public function test_cancelled_cannot_transition_to_done(): void
    {
        $svc = app(AppointmentServiceInterface::class);
        $a = $svc->createTicket(null);
        $svc->transitionStatus($a->id, AppointmentStatus::Cancelled);

        $this->expectException(DomainException::class);
        $svc->transitionStatus($a->id, AppointmentStatus::Done);
    }

    public function test_in_progress_to_waiting_clears_session_timestamps(): void
    {
        $svc = app(AppointmentServiceInterface::class);
        $a = $svc->createTicket(null);
        $svc->transitionStatus($a->id, AppointmentStatus::InProgress);
        $a->refresh();

        $a = $svc->transitionStatus($a->id, AppointmentStatus::Waiting);
        $this->assertSame(AppointmentStatus::Waiting, $a->status);
        $this->assertNull($a->started_at);
        $this->assertNull($a->completed_at);
    }

    public function test_same_status_transition_throws(): void
    {
        $svc = app(AppointmentServiceInterface::class);
        $a = $svc->createTicket(null);

        $this->expectException(DomainException::class);
        $svc->transitionStatus($a->id, AppointmentStatus::Waiting);
    }
}
