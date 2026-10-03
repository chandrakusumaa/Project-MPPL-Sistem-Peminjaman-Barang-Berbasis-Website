<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyPendingEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public $verificationUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $greeting = isset($notifiable->name) ? 'Halo ' . $notifiable->name . '!' : 'Halo!';
        
        return (new MailMessage)
                    ->subject('Verifikasi Alamat Email Baru')
                    ->greeting($greeting)
                    ->line('Kami menerima permintaan untuk mengganti alamat email Anda menjadi email ini.')
                    ->line('Silakan klik tombol di bawah ini untuk memverifikasi dan mengaktifkan email baru Anda.')
                    ->action('Verifikasi Email Baru', $this->verificationUrl)
                    ->line('Jika Anda tidak merasa meminta perubahan ini, Anda dapat mengabaikan email ini.');
    }
}
