<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WrongCostCenterNotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $employee;
    public $logoBase64;

    public function __construct($employee = null, $logoBase64 = null)
    {
        $this->employee = $employee;
        $this->logoBase64 = $logoBase64;

        $this->onQueue(config('queue.hcis_queue'));
    }

    public function build()
    {
        return $this->subject('Invalid Cost Center')->view('email.wrongCostCenterNotificationEmail');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invalid Cost Center',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.wrongCostCenterNotificationEmail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
