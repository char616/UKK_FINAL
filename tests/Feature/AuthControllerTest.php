<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_attempts_are_limited_by_login_key_and_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.submit'), [
                'login_key' => 'UNKNOWN-USER',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('login_key');
        }

        $this->post(route('login.submit'), [
            'login_key' => 'UNKNOWN-USER',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_login_key_longer_than_255_characters_is_rejected(): void
    {
        $this->post(route('login.submit'), [
            'login_key' => str_repeat('a', 256),
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('login_key');
    }

    public function test_password_longer_than_255_characters_is_rejected(): void
    {
        $this->post(route('login.submit'), [
            'login_key' => 'UNKNOWN-USER',
            'password' => str_repeat('a', 256),
        ])->assertSessionHasErrors('password');
    }
}
