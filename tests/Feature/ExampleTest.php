<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_queue(): void
    {
        $this->get('/')->assertRedirect('/queue');
    }
}
