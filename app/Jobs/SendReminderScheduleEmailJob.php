<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderScheduleEmail;

class SendReminderScheduleEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $name;
    protected $message;

    public function __construct($email, $name, $message)
    {
        $this->email = $email;
        $this->name = $name;
        $this->message = $message;
    }

    public function handle()
    {
        Mail::to($this->email)->send(new ReminderScheduleEmail($this->message, $this->name));
    }
}