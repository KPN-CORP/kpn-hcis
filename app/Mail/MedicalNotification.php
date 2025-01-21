<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $healthCoverages;
    public $logoBase64;

    public function __construct($healthCoverages, $logoBase64 = null)
    {
        $this->healthCoverages = $healthCoverages;
        $this->logoBase64 = $logoBase64;
    }

    public function build()
    {
        return $this->subject('New Business Trip Request')
            ->view('hcis.reimbursements.medical.approval.email.mdcNotification');
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medical Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.medical.approval.email.mdcNotification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
