<?php

namespace Tests\Unit;

use App\Auth\StrictTokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class StrictTokenGuardTest extends TestCase
{
    public function test_non_string_token_is_rejected_before_a_user_lookup(): void
    {
        $provider = new RecordingUserProvider;
        $request = Request::create('/api/user', 'GET', ['api_token' => ['bad']]);
        $guard = new StrictTokenGuard($provider, $request);

        $this->assertNull($guard->user());
        $this->assertFalse($guard->validate(['api_token' => ['bad']]));
        $this->assertSame([], $provider->lookups);
    }

    public function test_string_token_is_still_looked_up(): void
    {
        $provider = new RecordingUserProvider;
        $request = Request::create('/api/user', 'GET', ['api_token' => 'secret-token']);
        $guard = new StrictTokenGuard($provider, $request);

        $this->assertNull($guard->user());
        $this->assertFalse($guard->validate(['api_token' => 'secret-token']));
        $this->assertSame([
            ['api_token' => 'secret-token'],
            ['api_token' => 'secret-token'],
        ], $provider->lookups);
    }
}

class RecordingUserProvider implements UserProvider
{
    public array $lookups = [];

    public function retrieveById($identifier)
    {
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token) {}

    public function retrieveByCredentials(array $credentials)
    {
        $this->lookups[] = $credentials;

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }
}
