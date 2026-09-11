<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_health_check_is_ok(): void
    {
        $this->get('/up')->assertOk();
    }
}
