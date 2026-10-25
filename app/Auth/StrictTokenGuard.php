<?php

namespace App\Auth;

use Illuminate\Auth\TokenGuard;

/**
 * Token guard that only accepts a string API token.
 *
 * Laravel's TokenGuard below 12.68.0 / 13.29.0 can treat a non-string
 * credential as a match on MySQL and MariaDB (CVE-2026-114509). This app
 * does not enable that guard today. Registering this subclass keeps the
 * token driver from doing that lookup if it is turned on later.
 */
class StrictTokenGuard extends TokenGuard
{
    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;
        $token = $this->getTokenForRequest();

        if (is_string($token) && ! empty($token)) {
            $user = $this->provider->retrieveByCredentials([
                $this->storageKey => $this->hash ? hash('sha256', $token) : $token,
            ]);
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        $token = $credentials[$this->inputKey] ?? null;

        if (! is_string($token) || empty($token)) {
            return false;
        }

        if ($this->provider->retrieveByCredentials([$this->storageKey => $token])) {
            return true;
        }

        return false;
    }
}
