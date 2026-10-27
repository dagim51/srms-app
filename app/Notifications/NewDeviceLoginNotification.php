<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceLoginNotification extends Notification
{
    use Queueable;

    public string $ip;

    public string $device;

    public string $time;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $ip, string $device, ?string $time = null)
    {
        $this->ip = $ip;
        $this->device = $device;
        $this->time = $time ?? now()->toDateTimeString();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[SRMS Security Alert] New Device Login Detected')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your SRMS account was just accessed from an unrecognized browser or device.')
            ->line('Details:')
            ->line('• Device: '.$this->device)
            ->line('• IP Address: '.$this->ip)
            ->line('• Time: '.$this->time)
            ->line('If this was you, you can safely ignore this alert.')
            ->action('Review Account Security', url('/dashboard'))
            ->line('If you did not initiate this login, please reset your password immediately.')
            ->salutation('Regards, SRMS Security Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_device_login',
            'ip' => $this->ip,
            'device' => $this->device,
            'time' => $this->time,
        ];
    }
}
