<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HomeTripNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $noTktList;
    public $namaPenumpang;
    public $dariTkt;
    public $keTkt;
    public $tipeTkt;
    public $tglBrktTkt;
    public $jamBrktTkt;
    public $tglPlgTkt;
    public $jamPlgTkt;
    public $approvalStatus;
    public $managerName;

    public $approvalLink;
    public $rejectionLink;
    public function __construct(array $data)
    {
        $this->noTktList = $data['noTkt'];
        $this->namaPenumpang = $data['namaPenumpang'];
        $this->dariTkt = $data['dariTkt'];
        $this->keTkt = $data['keTkt'];
        $this->tipeTkt = $data['tipeTkt'];
        $this->tglBrktTkt = $data['tglBrktTkt'];
        $this->jamBrktTkt = $data['jamBrktTkt'];
        $this->tglPlgTkt = $data['tglPlgTkt'];
        $this->jamPlgTkt = $data['jamPlgTkt'];
        $this->approvalStatus = $data['approvalStatus'];
        $this->managerName = $data['managerName'];
        $this->approvalLink = $data['approvalLink'];
        $this->rejectionLink = $data['rejectionLink'];
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        return $this->subject('New Home Trip Request')
            ->view('hcis.reimbursements.homeTrip.email.htNotification');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Home Trip Request Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.homeTrip.email.htNotification',
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
