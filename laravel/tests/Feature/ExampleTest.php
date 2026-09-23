<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
