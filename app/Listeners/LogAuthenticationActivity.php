<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;
        
        ActivityLog::log(
            'login',
            "User {$user->name} berhasil login",
            null,
            ['user_agent' => request()->userAgent()]
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            /** @var User $user */
            $user = $event->user;
            
            ActivityLog::log(
                'logout',
                "User {$user->name} logout dari sistem",
                null,
                null
            );
        }
    }
}

