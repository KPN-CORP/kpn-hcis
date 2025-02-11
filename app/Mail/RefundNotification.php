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
    public $caDeclare;
    public $entDetails;
    public $entDeclare;
    public $employeeName;
    public $accNum;
    public $selisihCa;
    public $selisihEnt;
    public $isCa;
    public $isEnt;
    public $base64Image;

    /**
     * Create a new message instance.
     */
    public function __construct(
        BusinessTrip $businessTrip,
        $caDetails = null,
        $caDeclare = null,
        $entDetails = null,
        $entDeclare = null,
        $employeeName = null,
        $accNum = null,
        $selisihCa = null,
        $selisihEnt = null,
        $isCa = null,
        $isEnt = null,
        $base64Image = null,
    ) {
        $this->businessTrip = $businessTrip;
        $this->caDetails = $caDetails;
        $this->caDeclare = $caDeclare;
        $this->entDetails = $entDetails;
        $this->entDeclare = $entDeclare;
        $this->employeeName = $employeeName;
        $this->accNum = $accNum;
        $this->selisihCa = $selisihCa;
        $this->selisihEnt = $selisihEnt;
        $this->isCa = $isCa;
        $this->isEnt = $isEnt;
        $this->base64Image = $base64Image;
    }

    public function build()
    {
        return $this->view('hcis.reimbursements.businessTrip.email.refundNotification')
            ->with([
                'businessTrip' => $this->businessTrip,
                'caDetails' => $this->caDetails,
                'caDeclare' => $this->caDeclare,
                'entDetails' => $this->entDetails,
                'entDeclare' => $this->entDeclare,
                'employeeName' => $this->employeeName,
                'accNum' => $this->accNum,
                'selisihCa' => $this->selisihCa,
                'selisihEnt' => $this->selisihEnt,
                'isCa' => $this->isCa,
                'isEnt' => $this->isEnt,
                'base64Image' => $this->base64Image,
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
