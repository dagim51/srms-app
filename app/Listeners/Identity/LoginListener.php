<?php

namespace App\Listeners\Identity;

use App\Domains\Identity\Models\LoginEvent;
use App\Domains\Identity\Models\TrustedDevice;
use App\Models\User;
use App\Notifications\NewDeviceLoginNotification;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

class LoginListener
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip() ?? '127.0.0.1';
        $device = request()->userAgent() ?? 'Unknown Device';

        // 1. Record login event
        LoginEvent::create([
            'user_id' => $user->id,
            'event_type' => 'login_success',
            'ip' => $ip,
            'device' => $device,
            'success' => true,
            'occurred_at' => now(),
        ]);

        // 2. Anomaly detection: Check trusted devices
        $fingerprint = hash('sha256', $user->id.'|'.$device);

        $trusted = TrustedDevice::where('user_id', $user->id)
            ->where('fingerprint_hash', $fingerprint)
            ->first();

        if ($trusted) {
            if (! $trusted->isRevoked()) {
                $trusted->update(['last_seen_at' => now()]);
            }
        } else {
            // New unrecognized device: create record and alert user
            TrustedDevice::create([
                'user_id' => $user->id,
                'fingerprint_hash' => $fingerprint,
                'label' => substr($device, 0, 255),
                'last_seen_at' => now(),
            ]);

            // Dispatch security alert
            if (method_exists($user, 'notify')) {
                $user->notify(new NewDeviceLoginNotification($ip, $device));
            }
        }
    }

    /**
     * Handle failed login attempt.
     */
    public function handleFailed(Failed $event): void
    {
        $ip = request()->ip() ?? '127.0.0.1';
        $device = request()->userAgent() ?? 'Unknown Device';

        $userId = $event->user?->id;
        if (! $userId && isset($event->credentials['email'])) {
            $userId = User::where('email', $event->credentials['email'])->value('id');
        }

        LoginEvent::create([
            'user_id' => $userId,
            'event_type' => 'login_failed',
            'ip' => $ip,
            'device' => $device,
            'success' => false,
            'occurred_at' => now(),
        ]);
    }
}
