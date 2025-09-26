<?php

// ============================================
// app/Notifications/OverrideCodeExpiring.php
// ============================================

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationOverrideRequest;

class OverrideCodeExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    protected $overrideRequest;

    public function __construct(RegistrationOverrideRequest $overrideRequest)
    {
        $this->overrideRequest = $overrideRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ Override Code Expiring Soon')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your override code is expiring soon!')
            ->line('**Override Type:** ' . $this->overrideRequest->type_label)
            ->line('**Override Code:** `' . $this->overrideRequest->override_code . '`')
            ->line('**Expires:** ' . $this->overrideRequest->override_expires_at->format('M d, Y h:i A'))
            ->line('⏰ You have **' . $this->overrideRequest->override_expires_at->diffForHumans() . '** to use this code.')
            ->action('Register Now', url('/student/registration'))
            ->line('Once expired, you will need to submit a new override request.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'override_expiring',
            'request_id' => $this->overrideRequest->id,
            'override_code' => $this->overrideRequest->override_code,
            'expires_at' => $this->overrideRequest->override_expires_at,
            'message' => 'Your override code ' . $this->overrideRequest->override_code . ' expires soon',
            'url' => url('/student/registration')
        ];
    }
}