<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Plugin\Kernel\ServiceRegistry::class);
        $this->app->singleton(\App\Plugin\Kernel\RuntimeKernel::class);
    }

    public function boot(): void
    {
        \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url('/admin/reset-password/' . $token . '?email=' . urlencode($notifiable->getEmailForPasswordReset()));
        });

        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\ModuleEventSubscriber::class);
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\StaffCreated::class,
            \App\Listeners\HandleStaffCreated::class
        );
        
        try {
            $this->app->make(\App\Plugin\Kernel\RuntimeKernel::class)->bootstrap();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AppServiceProvider: Failed to bootstrap Plugin Runtime: " . $e->getMessage());
        }
    }
}
