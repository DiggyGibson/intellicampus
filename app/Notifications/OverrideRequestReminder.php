<?php

// ============================================
// app/Notifications/OverrideRequestReminder.php
// ============================================

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationOverrideRequest;
use Carbon\Carbon;

class OverrideRequestReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pendingRequests;
    protected $urgentRequests;

    public function __construct($pendingRequests, $urgentRequests = null)
    {
        $this->pendingRequests = $pendingRequests;
        $this->urgentRequests = $urgentRequests;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Pending Override Requests Require Your Attention')
            ->greeting('Hello ' . $notifiable->name . ',');
        
        if ($this->urgentRequests && $this->urgentRequests->count() > 0) {
            $message->line('**⚠️ You have ' . $this->urgentRequests->count() . ' URGENT request(s) from graduating seniors:**');
            
            foreach ($this->urgentRequests as $request) {
                $message->line('- ' . $request->student->user->name . ' (' . $request->type_label . ') - ' . 
                    Carbon::parse($request->created_at)->diffForHumans());
            }
        }
        
        $message->line('You have **' . $this->pendingRequests->count() . ' pending override request(s)** awaiting your review:');
        
        foreach ($this->pendingRequests->take(5) as $request) {
            $message->line('- ' . $request->student->user->name . ' (' . $request->type_label . ') - Submitted ' . 
                Carbon::parse($request->created_at)->diffForHumans());
        }
        
        if ($this->pendingRequests->count() > 5) {
            $message->line('...and ' . ($this->pendingRequests->count() - 5) . ' more.');
        }
        
        return $message->action('Review Pending Requests', url('/advisor/override-requests'))
            ->line('Please review these requests as soon as possible to help students with their registration.');
    }
}
