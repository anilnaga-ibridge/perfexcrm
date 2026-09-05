<?php

namespace App\Services\OAuth;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class TokenService
{
    protected string $cookieName = 'social_auth_token';

    public function generateCookieForUser(User $user): \Symfony\Component\HttpFoundation\Cookie
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        // Hide sensitive attributes before passing to frontend
        $userPayload = $user->makeHidden(['password', 'remember_token'])->toArray();

        $cookiePayload = json_encode([
            'token' => $token,
            'user'  => $userPayload,
        ]);

        $cookiePath = parse_url(url('/'), PHP_URL_PATH) . '/api/auth/social/exchange';

        return Cookie::make(
            $this->cookieName,
            $cookiePayload,
            5,
            $cookiePath,
            null,
            request()->secure(),
            true,
            false,
            'Lax'
        );
    }

    public function retrieveTokenAndUser(): ?array
    {
        $cookieValue = request()->cookie($this->cookieName);
        if (!$cookieValue) {
            return null;
        }

        $data = json_decode($cookieValue, true);
        if (!is_array($data) || !isset($data['token']) || !isset($data['user'])) {
            return null;
        }

        // Delete the cookie immediately (single-use exchange)
        Cookie::queue(Cookie::forget($this->cookieName, parse_url(url('/'), PHP_URL_PATH) . '/api/auth/social/exchange'));

        return $data;
    }
}
