<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_queue_page_is_french(): void
    {
        $this->get('/queue')->assertOk()->assertSee('Liste des patients', false);
    }

    public function test_locale_switch_sets_session_and_shows_arabic(): void
    {
        $this->get('/locale/ar')->assertRedirect();
        $this->get('/queue')->assertOk()->assertSee('قائمة المرضى', false);
    }

    public function test_invalid_locale_returns_404(): void
    {
        $this->get('/locale/de')->assertNotFound();
    }
}
