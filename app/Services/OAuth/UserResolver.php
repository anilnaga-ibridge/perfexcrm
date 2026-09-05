<?php

namespace App\Services\OAuth;

use App\Models\User;
use App\Models\SocialAccount;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserResolver
{
    public function resolve(string $provider, array $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            // 1. Look up by provider and provider ID
            $account = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser['id'])
                ->first();

            if ($account) {
                // Update tokens if they changed
                $account->update([
                    'token'         => $socialUser['token'] ?? $account->token,
                    'refresh_token' => $socialUser['refresh_token'] ?? $account->refresh_token,
                    'avatar'        => $socialUser['avatar'] ?? $account->avatar,
                ]);

                return $account->user;
            }

            // 2. Look up by email
            $user = User::where('email', $socialUser['email'])->first();

            if ($user) {
                // Link account to existing user
                SocialAccount::create([
                    'user_id'       => $user->id,
                    'provider'      => $provider,
                    'provider_id'   => $socialUser['id'],
                    'avatar'        => $socialUser['avatar'],
                    'token'         => $socialUser['token'],
                    'refresh_token' => $socialUser['refresh_token'],
                ]);

                return $user;
            }

            // 3. Register a new user
            $employeeRole = Role::where('slug', 'employee')->first();
            
            $user = User::create([
                'name'              => $socialUser['name'],
                'email'             => $socialUser['email'],
                'password'          => null, // Nullable password for social users
                'role'              => 'employee',
                'role_id'           => $employeeRole ? $employeeRole->id : null,
                'active'            => true,
                'profile_image'     => $socialUser['avatar'],
                'email_verified_at' => now(),
            ]);

            // Create social account linkage
            SocialAccount::create([
                'user_id'       => $user->id,
                'provider'      => $provider,
                'provider_id'   => $socialUser['id'],
                'avatar'        => $socialUser['avatar'],
                'token'         => $socialUser['token'],
                'refresh_token' => $socialUser['refresh_token'],
            ]);

            return $user;
        });
    }
}
