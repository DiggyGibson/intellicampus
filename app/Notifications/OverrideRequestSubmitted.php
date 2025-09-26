<?php
// ============================================
// app/Notifications/OverrideRequestSubmitted.php
// ============================================

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationOverrideRequest;

class OverrideRequestSubmitted extends Notification implements ShouldQueue
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
        $isStudent = $notifiable->id === $this->overrideRequest->student->user_id;
        
        if ($isStudent) {
            // Email to student confirming submission
            return (new MailMessage)
                ->subject('Override Request Submitted - ' . $this->overrideRequest->type_label)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your override request has been successfully submitted.')
                ->line('**Request Type:** ' . $this->overrideRequest->type_label)
                ->line('**Request ID:** #' . str_pad($this->overrideRequest->id, 6, '0', STR_PAD_LEFT))
                ->when($this->overrideRequest->request_type === 'credit_overload', function ($message) {
                    return $message->line('**Requested Credits:** ' . $this->overrideRequest->requested_credits);
                })
                ->when($this->overrideRequest->course, function ($message) {
                    return $message->line('**Course:** ' . $this->overrideRequest->course->code . ' - ' . $this->overrideRequest->course->title);
                })
                ->line('You will be notified once a decision has been made.')
                ->line('Expected response time: 2-3 business days')
                ->action('View Request Status', url('/student/override-requests'))
                ->line('If you have questions, please contact your advisor.');
        } else {
            // Email to approver about new request
            return (new MailMessage)
                ->subject('New Override Request Pending Review')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('A new override request requires your review.')
                ->line('**Student:** ' . $this->overrideRequest->student->user->name)
                ->line('**Student ID:** ' . $this->overrideRequest->student->student_id)
                ->line('**Request Type:** ' . $this->overrideRequest->type_label)
                ->when($this->overrideRequest->priority_level >= 8, function ($message) {
                    return $message->line('⚠️ **HIGH PRIORITY** - Graduating Senior');
                })
                ->line('**Submitted:** ' . $this->overrideRequest->created_at->format('M d, Y h:i A'))
                ->action('Review Request', url('/advisor/override-requests'))
                ->line('Please review this request at your earliest convenience.');
        }
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'override_request',
            'request_id' => $this->overrideRequest->id,
            'request_type' => $this->overrideRequest->request_type,
            'student_id' => $this->overrideRequest->student_id,
            'student_name' => $this->overrideRequest->student->user->name,
            'message' => 'New override request: ' . $this->overrideRequest->type_label,
            'url' => url('/advisor/override-requests')
        ];
    }
}