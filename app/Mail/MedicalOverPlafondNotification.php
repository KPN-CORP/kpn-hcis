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
    public $employee;
    public $totalUsage;
    public $logoBase64;

    public function __construct($healthPlafond, $healthPlan, $employee, $totalUsage, $logoBase64 = null)
    {
        $this->healthPlafond = $healthPlafond;
        $this->healthPlan = $healthPlan;
        $this->employee = $employee;
        $this->totalUsage = $totalUsage;
        $this->logoBase64 = $logoBase64;
    }

    public function build()
    {
        return $this->subject('Informasi Penggunaan Benefit Medical Melebihi Plafon')->view('hcis.reimbursements.medical.email.mdcOverPlafondNotification');
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Penggunaan Benefit Medical Melebihi Plafon',
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
