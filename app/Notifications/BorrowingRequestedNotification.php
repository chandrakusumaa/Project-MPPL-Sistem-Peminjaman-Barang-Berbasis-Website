<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowingRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Borrowing $borrowing)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailFor('borrowing')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('manage.borrowings.show', ['organization' => $this->borrowing->organization->slug, 'borrowing' => $this->borrowing->id]);
        
        return (new MailMessage)
                    ->subject('Pengajuan Peminjaman Baru')
                    ->greeting("Halo {$notifiable->name}!")
                    ->line("Pengguna {$this->borrowing->user->name} telah mengajukan peminjaman aset {$this->borrowing->asset->name}.")
                    ->line("Alasan: {$this->borrowing->reason}")
                    ->action('Lihat Peminjaman', $url)
                    ->line('Silakan segera tinjau permintaan ini.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengajuan Peminjaman Baru',
            'message' => "{$this->borrowing->user->name} ingin meminjam aset {$this->borrowing->asset->name}.",
            'url' => route('manage.borrowings.show', ['organization' => $this->borrowing->organization->slug, 'borrowing' => $this->borrowing->id]),
            'category' => 'borrowing',
            'resource_id' => $this->borrowing->id,
            'organization_id' => $this->borrowing->organization_id,
        ];
    }
}
