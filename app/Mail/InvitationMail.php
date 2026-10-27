<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable,SerializesModels;

    public function __construct(public string $link) {}

    public function build()
    {
        return $this->subject('Undangan Ruang Inventaris')->view('mail.invitation');
    }
}
