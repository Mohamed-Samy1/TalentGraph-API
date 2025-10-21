<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Customize the URL used in password reset emails for API/front-end apps
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $baseUrl = config('app.frontend_url', config('app.url'));
            $email = urlencode($notifiable->getEmailForPasswordReset());

            return rtrim($baseUrl, '/') . '/reset-password?token=' . $token . '&email=' . $email;
        });

        // For pure API backends: customize the email content to include raw token
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $apiBase = rtrim(config('app.url'), '/');
            $email = $notifiable->getEmailForPasswordReset();

            return (new MailMessage)
                ->subject('Your Password Reset Token')
                ->line('You requested a password reset. Use the token below to reset your password via the API:')
                ->line('Token: ' . $token)
                ->line('Email: ' . $email)
                ->line('API endpoint: POST ' . $apiBase . '/api/password/reset')
                ->line('Required JSON body:')
                ->line('{"email": "' . $email . '", "token": "' . $token . '", "password": "new-password", "password_confirmation": "new-password"}')
                ->line('If you did not request a password reset, no further action is required.');
        });
    }
}