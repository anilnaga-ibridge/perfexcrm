<?php

namespace App\Services\OAuth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OAuthService
{
    public function getRedirectUrl(string $provider): string
    {
        $provider = strtolower($provider);
        if (env('SOCIAL_AUTH_MOCK', false)) {
            return url("/api/auth/social/{$provider}/callback?code=mock_code&state=mock_state");
        }

        $config = ProviderFactory::getConfig($provider);

        // Generate and save state for CSRF protection
        $state = Str::random(40);
        session()->put("oauth_state_{$provider}", $state);

        $queryParams = [
            'client_id'     => $config['client_id'],
            'redirect_uri'  => $config['redirect_uri'],
            'response_type' => 'code',
            'state'         => $state,
        ];

        if ($provider === 'google') {
            $queryParams['scope'] = 'openid email profile';
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($queryParams);
        }

        if ($provider === 'facebook') {
            $queryParams['scope'] = 'email,public_profile';
            return 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query($queryParams);
        }

        if ($provider === 'apple') {
            $queryParams['scope']         = 'name email';
            $queryParams['response_mode'] = 'query';
            return 'https://appleid.apple.com/auth/authorize?' . http_build_query($queryParams);
        }

        throw new \InvalidArgumentException("Unsupported provider: {$provider}");
    }

    public function resolveUser(string $provider, ?string $code, ?string $state, ?string $appleUserJson = null): array
    {
        $provider = strtolower($provider);
        if (env('SOCIAL_AUTH_MOCK', false)) {
            return [
                'id'            => "mock_{$provider}_" . rand(1000, 9999),
                'email'         => "{$provider}-mock@example.com",
                'name'          => ucfirst($provider) . " Mock User",
                'avatar'        => "https://api.dicebear.com/7.x/adventurer/svg?seed=" . $provider,
                'token'         => 'mock_access_token',
                'refresh_token' => 'mock_refresh_token',
            ];
        }

        // Validate state
        $savedState = session()->pull("oauth_state_{$provider}");
        if (!$savedState || $savedState !== $state) {
            throw new \RuntimeException("Invalid OAuth state validation failed.");
        }

        if (!$code) {
            throw new \InvalidArgumentException("Authorization code is missing.");
        }

        $config = ProviderFactory::getConfig($provider);

        if ($provider === 'google') {
            return $this->resolveGoogleUser($code, $config);
        }

        if ($provider === 'facebook') {
            return $this->resolveFacebookUser($code, $config);
        }

        if ($provider === 'apple') {
            return $this->resolveAppleUser($code, $config, $appleUserJson);
        }

        throw new \InvalidArgumentException("Unsupported provider: {$provider}");
    }

    protected function resolveGoogleUser(string $code, array $config): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $config['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to exchange Google OAuth code: " . $response->body());
        }

        $tokens = $response->json();
        $accessToken = $tokens['access_token'];

        $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if ($userResponse->failed()) {
            throw new \RuntimeException("Failed to fetch Google user profile.");
        }

        $profile = $userResponse->json();

        return [
            'id'            => $profile['sub'],
            'email'         => $profile['email'],
            'name'          => $profile['name'] ?? ($profile['given_name'] . ' ' . ($profile['family_name'] ?? '')),
            'avatar'        => $profile['picture'] ?? null,
            'token'         => $accessToken,
            'refresh_token' => $tokens['refresh_token'] ?? null,
        ];
    }

    protected function resolveFacebookUser(string $code, array $config): array
    {
        $response = Http::get('https://graph.facebook.com/v12.0/oauth/access_token', [
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $config['redirect_uri'],
            'code'          => $code,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to exchange Facebook OAuth code: " . $response->body());
        }

        $tokens = $response->json();
        $accessToken = $tokens['access_token'];

        $userResponse = Http::get('https://graph.facebook.com/me', [
            'fields'       => 'id,name,email,picture.type(large)',
            'access_token' => $accessToken,
        ]);

        if ($userResponse->failed()) {
            throw new \RuntimeException("Failed to fetch Facebook user profile.");
        }

        $profile = $userResponse->json();

        return [
            'id'            => $profile['id'],
            'email'         => $profile['email'] ?? "fb-{$profile['id']}@facebook.com",
            'name'          => $profile['name'],
            'avatar'        => $profile['picture']['data']['url'] ?? null,
            'token'         => $accessToken,
            'refresh_token' => null,
        ];
    }

    protected function resolveAppleUser(string $code, array $config, ?string $appleUserJson = null): array
    {
        // For Apple, client secret is a signed JWT.
        // We look for APPLE_CLIENT_SECRET in .env, or generate it.
        $clientSecret = env('APPLE_CLIENT_SECRET');
        if (!$clientSecret) {
            $clientSecret = $this->generateAppleClientSecret();
        }

        $response = Http::asForm()->post('https://appleid.apple.com/auth/token', [
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $clientSecret,
            'redirect_uri'  => $config['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to exchange Apple OAuth code: " . $response->body());
        }

        $tokens = $response->json();
        $idToken = $tokens['id_token'];

        // Decode the id_token JWT (claims part)
        $claims = json_decode(base64_decode(explode('.', $idToken)[1]), true);
        $appleId = $claims['sub'];
        $email = $claims['email'] ?? null;

        // Extract name if provided in initial request (Apple only sends user details once)
        $name = 'Apple User';
        if ($appleUserJson) {
            $userData = json_decode($appleUserJson, true);
            if (isset($userData['name'])) {
                $name = trim(($userData['name']['firstName'] ?? '') . ' ' . ($userData['name']['lastName'] ?? ''));
            }
        }

        if (empty($name) && $email) {
            $name = explode('@', $email)[0];
        }

        return [
            'id'            => $appleId,
            'email'         => $email,
            'name'          => $name,
            'avatar'        => null,
            'token'         => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? null,
        ];
    }

    protected function generateAppleClientSecret(): string
    {
        // Simple fallback/stub for Apple client secret generation.
        // Developers can supply the pre-computed client secret via .env.
        throw new \RuntimeException("Apple client secret is not configured in .env (APPLE_CLIENT_SECRET).");
    }
}
