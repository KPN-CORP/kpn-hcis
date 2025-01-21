<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\BusinessTrip;

class RefundNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $businessTrip;
    public $caDetails;
    public $newDeclareCa;
    public $employeeName;
    public $accNum;
    public $selisih;

    /**
     * Create a new message instance.
     */
    public function __construct(
        BusinessTrip $businessTrip,
        $caDetails = null,
        $newDeclareCa = null,
        $employeeName = null,
        $accNum = null,
        $selisih = null,
    ) {
        $this->businessTrip = $businessTrip;
        $this->caDetails = $caDetails;
        $this->newDeclareCa = $newDeclareCa;
        $this->employeeName = $employeeName;
        $this->accNum = $accNum;
        $this->selisih = $selisih;
    }

    public function build()
    {
        return $this->view('hcis.reimbursements.businessTrip.email.refundNotification')
            ->with([
                'businessTrip' => $this->businessTrip,
                'caDetails' => $this->caDetails,
                'newDeclareCa' => $this->newDeclareCa,
                'employeeName' => $this->employeeName,
                'accNum' => $this->accNum,
                'selisih' => $this->selisih,
            ]);
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Refund Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hcis.reimbursements.businessTrip.email.refundNotification',
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
