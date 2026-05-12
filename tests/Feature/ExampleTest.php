<?php

namespace Tests\Feature;

use App\Entities\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_queue_when_authenticated(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertRedirect('/queue');
    }
}
