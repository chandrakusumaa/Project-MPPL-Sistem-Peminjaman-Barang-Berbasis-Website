<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowingReviewedNotification extends Notification implements ShouldQueue
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
        $statusStr = $this->borrowing->status->value === 'borrowed' ? 'Disetujui' : 'Ditolak';
        $url = route('my-borrowings.show', $this->borrowing->id);
        
        $message = (new MailMessage)
                    ->subject("Peminjaman Anda $statusStr")
                    ->greeting("Halo {$notifiable->name}!")
                    ->line("Pengajuan peminjaman Anda untuk aset {$this->borrowing->asset->name} di organisasi {$this->borrowing->organization->name} telah $statusStr.");

        if ($this->borrowing->status->value === 'rejected' && $this->borrowing->rejection_reason) {
            $message->line("Alasan Penolakan: {$this->borrowing->rejection_reason}");
        }

        return $message->action('Lihat Peminjaman', $url)
                       ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toDatabase(object $notifiable): array
    {
        $statusStr = $this->borrowing->status->value === 'borrowed' ? 'Disetujui' : 'Ditolak';

        return [
            'title' => "Peminjaman $statusStr",
            'message' => "Pengajuan Anda untuk aset {$this->borrowing->asset->name} telah $statusStr.",
            'url' => route('my-borrowings.show', $this->borrowing->id),
            'category' => 'borrowing',
            'resource_id' => $this->borrowing->id,
            'organization_id' => $this->borrowing->organization_id,
        ];
    }
}
