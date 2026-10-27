<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $event, public string $url)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Organization Inventory: '.$this->event)->line('Ada pembaruan aktivitas di Ruang Inventaris Anda.')->action('Lihat ruang', $this->url);
    }
}
