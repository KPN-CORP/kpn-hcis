<?php

namespace App\Mail;

use App\Models\BusinessTrip;
use App\Models\Hotel;
use App\Models\Tiket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessTripNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $businessTrip;
    public $hotelDetails;
    public $ticketDetails;
    public $taksiDetails;
    public $caDetails;
    public $managerName;
    public $approvalLink;
    public $rejectionLink;
    public $employeeName;
    public $base64Image;
    public $textNotification;

    /**
     * Create a new message instance.
     */
    public function __construct(
        BusinessTrip $businessTrip,
        $hotelDetails = null,
        $ticketDetails = null,
        $taksiDetails = null,
        $caDetails = null,
        $managerName = null,
        $approvalLink = null,
        $rejectionLink = null,
        $employeeName = null,
        $base64Image = null,
        $textNotification = null,
    ) {
        $this->businessTrip = $businessTrip;
        $this->hotelDetails = $hotelDetails;
        $this->ticketDetails = $ticketDetails;
        $this->taksiDetails = $taksiDetails;
        $this->caDetails = $caDetails;
        $this->managerName = $managerName;
        $this->approvalLink = $approvalLink;
        $this->rejectionLink = $rejectionLink;
        $this->employeeName = $employeeName;
        $this->base64Image = $base64Image;
        $this->textNotification = $textNotification;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('hcis.reimbursements.businessTrip.email.btNotification')
            ->with([
                'businessTrip' => $this->businessTrip,
                'hotelDetails' => $this->hotelDetails, // Use the passed hotel details
                'ticketDetails' => $this->ticketDetails, // Use the passed ticket details
                'taksiDetails' => $this->taksiDetails,
                'caDetails' => $this->caDetails,
                'managerName' => $this->managerName,
                'approvalLink' => $this->approvalLink,
                'rejectionLink' => $this->rejectionLink,
                'employeeName' => $this->employeeName,
                'base64Image' => $this->base64Image,
                'textNotification' => $this->textNotification,
            ]);
    }

    /**
     * Get the email envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Business Trip Notification',
        );
    }

    /**
     * Get the email content.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.businessTrip.email.btNotification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
