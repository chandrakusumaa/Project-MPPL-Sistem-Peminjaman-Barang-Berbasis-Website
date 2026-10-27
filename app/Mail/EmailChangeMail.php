<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailChangeMail extends Mailable
{
    use Queueable,SerializesModels;

    public function __construct(public string $link) {}

    public function build()
    {
        return $this->subject('Verifikasi email baru Organization Inventory')->view('mail.email-change');
    }
}
