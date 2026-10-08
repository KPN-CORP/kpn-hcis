<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CADeclarationReminderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $employee;
    public $transaction;
    public $logoBase64;

    public function __construct($employee = null, $transaction = null, $logoBase64 = null)
    {
        $this->employee = $employee;
        $this->transaction = $transaction;
        $this->logoBase64 = $logoBase64;

        $this->onQueue(config('queue.hcis_queue'));
    }

    public function build()
    {
        return $this->subject('Reminder – Cash Advance Declaration')->view('hcis.reimbursements.cashadv.email.caDeclarationReminderNotification');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder – Cash Advance Declaration',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.cashadv.email.caDeclarationReminderNotification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
