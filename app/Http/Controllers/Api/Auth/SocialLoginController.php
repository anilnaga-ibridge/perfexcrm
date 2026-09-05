<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuth\ProviderFactory;
use App\Services\OAuth\OAuthService;
use App\Services\OAuth\UserResolver;
use App\Services\OAuth\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends Controller
{
    protected OAuthService $oauthService;
    protected UserResolver $userResolver;
    protected TokenService $tokenService;

    public function __construct(
        OAuthService $oauthService,
        UserResolver $userResolver,
        TokenService $tokenService
    ) {
        $this->oauthService = $oauthService;
        $this->userResolver = $userResolver;
        $this->tokenService = $tokenService;
    }

    protected function checkEnabled(): ?\Illuminate\Http\JsonResponse
    {
        if (!env('SOCIAL_AUTH_ENABLED', true)) {
            return response()->json([
                'message' => 'Social login is currently disabled.'
            ], 403);
        }
        return null;
    }

    protected function validateProvider(string $provider): ?\Illuminate\Http\JsonResponse
    {
        if (!ProviderFactory::isValidProvider($provider)) {
            return response()->json([
                'message' => 'Unsupported social login provider.'
            ], 404);
        }
        return null;
    }

    public function redirect(Request $request, string $provider)
    {
        if ($error = $this->checkEnabled()) return $error;
        if ($error = $this->validateProvider($provider)) return $error;

        try {
            $redirectUrl = $this->oauthService->getRedirectUrl($provider);
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error("OAuth redirect failed for {$provider}: " . $e->getMessage());
            return redirect('/admin/login?error=' . urlencode('Social redirect failed.'));
        }
    }

    public function callback(Request $request, string $provider)
    {
        if ($error = $this->checkEnabled()) return $error;
        if ($error = $this->validateProvider($provider)) return $error;

        // Apple payload can have extra 'user' json field in POST/GET
        $appleUser = $request->input('user');

        try {
            $socialUser = $this->oauthService->resolveUser(
                $provider,
                $request->input('code'),
                $request->input('state'),
                $appleUser
            );

            $user = $this->userResolver->resolve($provider, $socialUser);

            // Log account active status check
            if (isset($user->active) && !$user->active) {
                return redirect('/admin/login?error=' . urlencode('Your account is deactivated.'));
            }

            // Update user last login
            $user->update(['last_login' => now()]);

            $cookie = $this->tokenService->generateCookieForUser($user);

            // Redirect back to frontend login with callback flag and queue the HTTP-only cookie
            return redirect('/admin/login?social_callback=true')
                ->withCookie($cookie);

        } catch (\Exception $e) {
            Log::error("OAuth callback failed for {$provider}: " . $e->getMessage());
            return redirect('/admin/login?error=' . urlencode($e->getMessage() ?: 'Authentication failed.'));
        }
    }

    public function exchange(Request $request)
    {
        if ($error = $this->checkEnabled()) return $error;

        try {
            $data = $this->tokenService->retrieveTokenAndUser();

            if (!$data) {
                return response()->json([
                    'message' => 'Social login session has expired or is invalid.'
                ], 401);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error("OAuth exchange failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve social auth credentials.'
            ], 401);
        }
    }
}
