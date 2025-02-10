<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageContent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($messageContent)
    {
        $this->messageContent = $messageContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Test Email Laravel')
                    ->view('email.test_email')
                    ->with([
                        'messageContent' => $this->messageContent,
                    ]);
    }
}
