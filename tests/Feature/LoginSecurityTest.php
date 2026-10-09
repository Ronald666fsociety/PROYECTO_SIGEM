<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login'), [
                'email' => 'usuario@ejemplo.test',
                'password' => 'incorrecta',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => 'usuario@ejemplo.test',
            'password' => 'incorrecta',
        ])->assertTooManyRequests();
    }
}
