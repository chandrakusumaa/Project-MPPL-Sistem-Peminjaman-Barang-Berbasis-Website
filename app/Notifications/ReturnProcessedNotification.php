<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnProcessedNotification extends Notification implements ShouldQueue
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
        $url = route('my-borrowings.show', $this->borrowing->id);
        
        $message = (new MailMessage)
                    ->subject('Pengembalian Aset Berhasil Diproses')
                    ->greeting("Halo {$notifiable->name}!")
                    ->line("Pengembalian aset {$this->borrowing->asset->name} Anda telah diproses oleh Admin/Staff.")
                    ->line("Kondisi pengembalian: " . $this->borrowing->return_condition->label());

        if ($this->borrowing->fine_amount > 0) {
            $message->line("Denda Keterlambatan: Rp " . number_format($this->borrowing->fine_amount, 0, ',', '.'));
        }

        return $message->action('Lihat Detail', $url)
                       ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengembalian Diproses',
            'message' => "Pengembalian aset {$this->borrowing->asset->name} Anda telah diproses.",
            'url' => route('my-borrowings.show', $this->borrowing->id),
            'category' => 'borrowing',
            'resource_id' => $this->borrowing->id,
            'organization_id' => $this->borrowing->organization_id,
        ];
    }
}
