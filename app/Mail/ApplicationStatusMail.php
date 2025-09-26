<?php

namespace App\Mail;

use App\Models\AdmissionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $subject;
    public $messageContent;
    public $priority;

    /**
     * Create a new message instance.
     *
     * @param AdmissionApplication $application
     * @param string $subject
     * @param string $message
     * @param string $priority
     */
    public function __construct(
        AdmissionApplication $application, 
        string $subject, 
        string $message, 
        string $priority = 'normal'
    ) {
        $this->application = $application;
        $this->subject = $subject;
        $this->messageContent = $message;
        $this->priority = $priority;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->subject)
                     ->view('emails.application-status')
                     ->with([
                         'application' => $this->application,
                         'messageContent' => $this->messageContent,
                     ]);

        if ($this->priority === 'high') {
            $mail->priority(1);
        }

        return $mail;
    }
}