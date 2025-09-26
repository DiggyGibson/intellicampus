<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class ApplicationCommunication extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_communications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'communication_type',
        'direction',
        'subject',
        'message',
        'recipient_email',
        'recipient_phone',
        'sender_name',
        'sender_id',
        'status',
        'template_used',
        'template_variables',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'template_variables' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime'
    ];

    /**
     * Email templates for different communication scenarios.
     */
    protected static $emailTemplates = [
        'application_received' => [
            'subject' => 'Application Received - {application_number}',
            'template' => 'emails.admissions.application-received'
        ],
        'application_incomplete' => [
            'subject' => 'Action Required: Complete Your Application',
            'template' => 'emails.admissions.application-incomplete'
        ],
        'document_requested' => [
            'subject' => 'Document Required for Your Application',
            'template' => 'emails.admissions.document-requested'
        ],
        'interview_scheduled' => [
            'subject' => 'Interview Scheduled for {date}',
            'template' => 'emails.admissions.interview-scheduled'
        ],
        'decision_notification' => [
            'subject' => 'Admission Decision - {program_name}',
            'template' => 'emails.admissions.decision-notification'
        ],
        'enrollment_reminder' => [
            'subject' => 'Reminder: Enrollment Deadline Approaching',
            'template' => 'emails.admissions.enrollment-reminder'
        ],
        'deposit_confirmation' => [
            'subject' => 'Enrollment Deposit Received',
            'template' => 'emails.admissions.deposit-confirmation'
        ],
        'waitlist_notification' => [
            'subject' => 'Waitlist Update - {program_name}',
            'template' => 'emails.admissions.waitlist-notification'
        ]
    ];

    /**
     * SMS templates for different scenarios.
     */
    protected static $smsTemplates = [
        'application_received' => 'Your application {application_number} has been received. Check your email for details.',
        'document_reminder' => 'Reminder: Please submit missing documents for your application {application_number}.',
        'interview_reminder' => 'Reminder: Your interview is scheduled for {date} at {time}.',
        'decision_ready' => 'Your admission decision is ready. Please check your application portal.',
        'enrollment_deadline' => 'Your enrollment deadline is {date}. Please confirm your enrollment.',
        'deposit_received' => 'Your enrollment deposit has been received. Welcome to {institution_name}!'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default status
        static::creating(function ($communication) {
            if (!$communication->status) {
                $communication->status = 'pending';
            }
            
            // Set sender name if sender_id is provided
            if ($communication->sender_id && !$communication->sender_name) {
                $sender = User::find($communication->sender_id);
                $communication->sender_name = $sender ? $sender->name : 'System';
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application for this communication.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the sender user.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for outbound communications.
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    /**
     * Scope for inbound communications.
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope for communications by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('communication_type', $type);
    }

    /**
     * Scope for sent communications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for pending communications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed communications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for opened emails.
     */
    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    /**
     * Helper Methods
     */

    /**
     * Send the communication.
     */
    public function send(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $result = match($this->communication_type) {
            'email' => $this->sendEmail(),
            'sms' => $this->sendSms(),
            'portal_message' => $this->sendPortalMessage(),
            default => false
        };
        
        if ($result) {
            $this->status = 'sent';
            $this->sent_at = now();
        } else {
            $this->status = 'failed';
        }
        
        return $this->save();
    }

    /**
     * Send email communication.
     */
    protected function sendEmail(): bool
    {
        try {
            // Get template if specified
            if ($this->template_used && isset(self::$emailTemplates[$this->template_used])) {
                $template = self::$emailTemplates[$this->template_used];
                $subject = $this->replaceVariables($template['subject']);
                
                // Send using Laravel Mail
                // Mail::to($this->recipient_email)
                //     ->send(new ApplicationEmail($template['template'], $this->template_variables, $subject));
            } else {
                // Send plain text email
                // Mail::raw($this->message, function ($message) {
                //     $message->to($this->recipient_email)
                //            ->subject($this->subject);
                // });
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS communication.
     */
    protected function sendSms(): bool
    {
        try {
            // Get SMS content
            $message = $this->message;
            
            if ($this->template_used && isset(self::$smsTemplates[$this->template_used])) {
                $message = $this->replaceVariables(self::$smsTemplates[$this->template_used]);
            }
            
            // Send via SMS provider (e.g., Twilio, AfricasTalking)
            // Example with HTTP client:
            // $response = Http::post('https://api.smsprovider.com/send', [
            //     'to' => $this->recipient_phone,
            //     'message' => $message
            // ]);
            
            return true; // Return actual result from SMS provider
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send portal message.
     */
    protected function sendPortalMessage(): bool
    {
        // This would create an in-app notification
        // For now, just mark as sent
        return true;
    }

    /**
     * Replace template variables.
     */
    protected function replaceVariables($template): string
    {
        if (!$this->template_variables) {
            return $template;
        }
        
        foreach ($this->template_variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }

    /**
     * Mark as delivered.
     */
    public function markAsDelivered(): bool
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        
        return $this->save();
    }

    /**
     * Mark as opened.
     */
    public function markAsOpened(): bool
    {
        if (!$this->opened_at) {
            $this->status = 'opened';
            $this->opened_at = now();
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Mark as clicked.
     */
    public function markAsClicked(): bool
    {
        if (!$this->clicked_at) {
            $this->status = 'clicked';
            $this->clicked_at = now();
            
            if (!$this->opened_at) {
                $this->opened_at = now();
            }
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Mark as bounced.
     */
    public function markAsBounced(): bool
    {
        $this->status = 'bounced';
        
        return $this->save();
    }

    /**
     * Resend the communication.
     */
    public function resend(): bool
    {
        $this->status = 'pending';
        $this->sent_at = null;
        $this->delivered_at = null;
        $this->opened_at = null;
        $this->clicked_at = null;
        
        if ($this->save()) {
            return $this->send();
        }
        
        return false;
    }

    /**
     * Get communication type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->communication_type) {
            'email' => 'Email',
            'sms' => 'SMS',
            'letter' => 'Letter',
            'portal_message' => 'Portal Message',
            'phone_call' => 'Phone Call',
            default => ucfirst(str_replace('_', ' ', $this->communication_type))
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'sent' => 'blue',
            'delivered' => 'indigo',
            'opened' => 'purple',
            'clicked' => 'green',
            'failed' => 'red',
            'bounced' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Get direction label.
     */
    public function getDirectionLabel(): string
    {
        return $this->direction === 'outbound' ? 'Sent' : 'Received';
    }

    /**
     * Get direction icon.
     */
    public function getDirectionIcon(): string
    {
        return $this->direction === 'outbound' ? 'arrow-up-right' : 'arrow-down-left';
    }

    /**
     * Calculate engagement score.
     */
    public function getEngagementScore(): int
    {
        $score = 0;
        
        if ($this->status === 'sent') $score += 25;
        if ($this->status === 'delivered' || $this->delivered_at) $score += 25;
        if ($this->status === 'opened' || $this->opened_at) $score += 25;
        if ($this->status === 'clicked' || $this->clicked_at) $score += 25;
        
        return $score;
    }

    /**
     * Get delivery time in hours.
     */
    public function getDeliveryTime(): ?float
    {
        if ($this->sent_at && $this->delivered_at) {
            return round($this->sent_at->diffInMinutes($this->delivered_at) / 60, 2);
        }
        
        return null;
    }

    /**
     * Get open rate time in hours.
     */
    public function getOpenTime(): ?float
    {
        if ($this->delivered_at && $this->opened_at) {
            return round($this->delivered_at->diffInMinutes($this->opened_at) / 60, 2);
        }
        
        return null;
    }

    /**
     * Create a follow-up communication.
     */
    public function createFollowUp($type, $message, $templateName = null): self
    {
        return self::create([
            'application_id' => $this->application_id,
            'communication_type' => $type,
            'direction' => 'outbound',
            'subject' => 'Follow-up: ' . $this->subject,
            'message' => $message,
            'recipient_email' => $this->recipient_email,
            'recipient_phone' => $this->recipient_phone,
            'sender_id' => auth()->id(),
            'template_used' => $templateName,
            'template_variables' => $this->template_variables
        ]);
    }

    /**
     * Get communication statistics for an application.
     */
    public static function getApplicationStats($applicationId): array
    {
        $communications = self::where('application_id', $applicationId)->get();
        
        return [
            'total' => $communications->count(),
            'sent' => $communications->where('status', 'sent')->count(),
            'delivered' => $communications->where('status', 'delivered')->count(),
            'opened' => $communications->whereNotNull('opened_at')->count(),
            'clicked' => $communications->whereNotNull('clicked_at')->count(),
            'failed' => $communications->where('status', 'failed')->count(),
            'by_type' => [
                'email' => $communications->where('communication_type', 'email')->count(),
                'sms' => $communications->where('communication_type', 'sms')->count(),
                'portal' => $communications->where('communication_type', 'portal_message')->count(),
            ],
            'by_direction' => [
                'outbound' => $communications->where('direction', 'outbound')->count(),
                'inbound' => $communications->where('direction', 'inbound')->count(),
            ],
            'average_open_time' => $communications->avg(function ($comm) {
                return $comm->getOpenTime();
            }),
            'last_communication' => $communications->sortByDesc('created_at')->first()?->created_at
        ];
    }

    /**
     * Schedule a communication for later.
     */
    public function schedule($sendAt): bool
    {
        $this->status = 'scheduled';
        $this->sent_at = $sendAt;
        
        return $this->save();
    }

    /**
     * Check if communication can be sent.
     */
    public function canSend(): bool
    {
        // Check if has recipient
        if ($this->communication_type === 'email' && !$this->recipient_email) {
            return false;
        }
        
        if ($this->communication_type === 'sms' && !$this->recipient_phone) {
            return false;
        }
        
        // Check if not already sent
        if (in_array($this->status, ['sent', 'delivered', 'opened', 'clicked'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Generate communication summary.
     */
    public function generateSummary(): array
    {
        return [
            'type' => $this->communication_type,
            'direction' => $this->direction,
            'status' => $this->status,
            'subject' => $this->subject,
            'recipient' => $this->recipient_email ?? $this->recipient_phone,
            'sender' => $this->sender_name,
            'template' => $this->template_used,
            'timestamps' => [
                'created' => $this->created_at?->format('Y-m-d H:i:s'),
                'sent' => $this->sent_at?->format('Y-m-d H:i:s'),
                'delivered' => $this->delivered_at?->format('Y-m-d H:i:s'),
                'opened' => $this->opened_at?->format('Y-m-d H:i:s'),
                'clicked' => $this->clicked_at?->format('Y-m-d H:i:s'),
            ],
            'metrics' => [
                'delivery_time_hours' => $this->getDeliveryTime(),
                'open_time_hours' => $this->getOpenTime(),
                'engagement_score' => $this->getEngagementScore()
            ]
        ];
    }
}