<?php

namespace Tests\Feature;

use App\Auth\StrictTokenGuard;
use Tests\TestCase;

class StrictTokenGuardRegistrationTest extends TestCase
{
    public function test_token_driver_uses_the_strict_guard(): void
    {
        config(['auth.guards.api' => [
            'driver' => 'token',
            'provider' => 'users',
        ]]);

        $this->assertInstanceOf(StrictTokenGuard::class, auth()->guard('api'));
    }
}
