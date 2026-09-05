<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     * Restricted: only admins can create new staff accounts via API.
     */
    public function register(Request $request)
    {
        // Only authenticated admins can register new staff
        $user = $request->user();
        if (!$user || !is_admin()) {
            return response()->json([
                'message' => 'Only administrators can create new staff accounts'
            ], 403);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'  => ['nullable', 'exists:roles,id'],
        ]);

        $data = [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if (!empty($validated['role_id'])) {
            $data['role_id'] = $validated['role_id'];
            $role = \App\Models\Role::find($validated['role_id']);
            $data['role'] = $role ? $role->slug : 'employee';
        } else {
            $data['role'] = 'employee';
        }

        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->makeHidden(['password', 'remember_token']),
        ], 201);
    }

    /**
     * Handle authentication login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::with('role')->where('email', $request['email'])->firstOrFail();

        // Check if account is active
        if (isset($user->active) && !$user->active) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact an administrator.'
            ], 403);
        }

        $user->update(['last_login' => now()]);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->makeHidden(['password', 'remember_token']),
        ]);
    }

    /**
     * Handle authentication logout request.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revoked successfully'
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->load('role');
        }
        return response()->json([
            'user' => $user ? $user->makeHidden(['password', 'remember_token']) : null
        ]);
    }

    /**
     * Send password reset link to the given email.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent! Please check your email inbox.',
            ]);
        }

        $message = match ($status) {
            Password::RESET_THROTTLED => 'Please wait a moment before requesting another password reset link.',
            Password::INVALID_USER => 'We could not find an account registered with that email address.',
            default => 'Unable to send reset link. Please verify your email address and try again.',
        };

        return response()->json(['message' => $message], 400);
    }

    /**
     * Reset the password using a valid token.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully! You can now log in.',
            ]);
        }

        $message = match ($status) {
            Password::INVALID_TOKEN => 'This password reset link is invalid or has expired. Please request a new link.',
            Password::INVALID_USER => 'We could not find an account associated with this email address.',
            Password::RESET_THROTTLED => 'Please wait a moment before retrying.',
            default => 'Failed to reset password. Please try again.',
        };

        return response()->json(['message' => $message], 400);
    }
}
