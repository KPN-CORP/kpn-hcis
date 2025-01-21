<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\BusinessTrip;

class DeclarationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $businessTrip;
    public $caDetails;
    public $caDeclare;
    public $managerName;
    public $approvalLink;
    public $rejectionLink;

    public function __construct(
        BusinessTrip $businessTrip,
        $caDetails = null,
        $caDeclare = null,
        $managerName = null,
        $approvalLink = null,
        $rejectionLink = null
    ) {
        $this->businessTrip = $businessTrip;
        $this->caDetails = $caDetails;
        $this->caDeclare = $caDeclare;
        $this->managerName = $managerName;
        $this->approvalLink = $approvalLink;
        $this->rejectionLink = $rejectionLink;
    }

    public function build()
    {
        return $this->view('hcis.reimbursements.businessTrip.email.btDeclareNotification')
            ->with([
                'businessTrip' => $this->businessTrip,
                'caDetails' => $this->caDetails,
                'caDeclare' => $this->caDeclare,
                'managerName' => $this->managerName,
                'approvalLink' => $this->approvalLink,
                'rejectionLink' => $this->rejectionLink,
            ]);
    }

    /**
     * Get the email envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Business Trip Declaration Notification',
        );
    }

    /**
     * Get the email content.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.businessTrip.email.btDeclareNotification',
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
