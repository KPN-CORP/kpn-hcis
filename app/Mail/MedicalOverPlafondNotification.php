<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicalOverPlafondNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $healthPlafond;
    public $healthPlan;
    public $logoBase64;

    public function __construct($healthPlafond, $healthPlan, $logoBase64 = null)
    {
        $this->healthPlafond = $healthPlafond;
        $this->healthPlan = $healthPlan;
        $this->logoBase64 = $logoBase64;
    }

    public function build()
    {
        return $this->subject('Medical Over Plafond Notification')->view('hcis.reimbursements.medical.email.mdcOverPlafondNotification');
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medical Over Plafond Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.medical.email.mdcOverPlafondNotification',
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
