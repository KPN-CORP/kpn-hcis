<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $noSppd;
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

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->noSppd = $data['noSppd'];
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

    public function build()
    {
        return $this->subject('New Ticket Request')
            ->view('hcis.reimbursements.businessTrip.email.tktNotification');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket Request Notification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.businessTrip.email.tktNotification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
