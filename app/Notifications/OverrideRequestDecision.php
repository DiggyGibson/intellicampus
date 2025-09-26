<?php

// ============================================
// app/Notifications/OverrideRequestDecision.php
// ============================================

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationOverrideRequest;

class OverrideRequestDecision extends Notification implements ShouldQueue
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
        $isApproved = $this->overrideRequest->status === 'approved';
        
        $message = (new MailMessage)
            ->subject('Override Request ' . ucfirst($this->overrideRequest->status) . ' - ' . $this->overrideRequest->type_label)
            ->greeting('Hello ' . $notifiable->name . ',');
        
        if ($isApproved) {
            $message->line('Good news! Your override request has been **APPROVED**.')
                ->line('**Request Type:** ' . $this->overrideRequest->type_label)
                ->line('**Override Code:** `' . $this->overrideRequest->override_code . '`')
                ->line('**Valid Until:** ' . $this->overrideRequest->override_expires_at->format('M d, Y h:i A'))
                ->line('### How to Use Your Override Code:')
                ->line('1. Go to the registration system')
                ->line('2. Add your desired course(s) to your cart')
                ->line('3. Enter the override code when prompted')
                ->line('4. Complete your registration');
                
            if ($this->overrideRequest->conditions) {
                $message->line('### Conditions:')
                    ->line($this->overrideRequest->conditions);
            }
            
            if ($this->overrideRequest->approver_notes) {
                $message->line('### Approver Notes:')
                    ->line($this->overrideRequest->approver_notes);
            }
        } else {
            $message->line('Your override request has been **DENIED**.')
                ->line('**Request Type:** ' . $this->overrideRequest->type_label);
                
            if ($this->overrideRequest->approver_notes) {
                $message->line('### Reason for Denial:')
                    ->line($this->overrideRequest->approver_notes);
            }
            
            $message->line('### Next Steps:')
                ->line('- Review the feedback provided')
                ->line('- Contact your advisor to discuss alternatives')
                ->line('- Consider selecting different courses')
                ->line('- You may submit a new request with additional justification if circumstances change');
        }
        
        return $message->action('View Request Details', url('/student/override-requests'))
            ->line('If you have questions about this decision, please contact your advisor.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'override_decision',
            'request_id' => $this->overrideRequest->id,
            'request_type' => $this->overrideRequest->request_type,
            'status' => $this->overrideRequest->status,
            'override_code' => $this->overrideRequest->override_code,
            'message' => 'Your ' . $this->overrideRequest->type_label . ' request has been ' . $this->overrideRequest->status,
            'url' => url('/student/override-requests')
        ];
    }
}