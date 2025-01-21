<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CashAdvancedReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $ca_transaction;
    public $textNotification;
    public $reminder;
    public $logoBase64;
    public $declaration;

    public function __construct($ca_transaction, $textNotification, $reminder, $logoBase64 = null, $declaration)
    {
        $this->logoBase64 = $logoBase64;

        $this->ca_transaction = $ca_transaction;
        $this->textNotification = $textNotification;
        $this->reminder = $reminder;
        $this->declaration = $declaration;
    }

    public function build()
    {
        return $this->subject('Cash Advanced Reminder')
            ->view('email.reminderCashAdvanced');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cash Advanced Reminder',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.reminderCashAdvanced',
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
