<?php

namespace App\Services\OAuth;

class ProviderFactory
{
    protected static array $supportedProviders = ['google', 'facebook', 'apple'];

    public static function isValidProvider(string $provider): bool
    {
        return self::in_class_supported($provider);
    }

    protected static function in_class_supported(string $provider): bool
    {
        return in_array(strtolower($provider), self::$supportedProviders, true);
    }

    public static function getConfig(string $provider): array
    {
        $provider = strtolower($provider);
        if (!self::in_class_supported($provider)) {
            throw new \InvalidArgumentException("Unsupported OAuth provider: {$provider}");
        }

        return [
            'client_id'     => env(strtoupper($provider) . '_CLIENT_ID'),
            'client_secret' => env(strtoupper($provider) . '_CLIENT_SECRET'),
            'redirect_uri'  => env(strtoupper($provider) . '_REDIRECT_URI', url("/api/auth/social/{$provider}/callback")),
        ];
    }
}
