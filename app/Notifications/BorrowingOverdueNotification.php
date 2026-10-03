<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowingOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Borrowing $borrowing, public bool $isAdmin)
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
        if ($this->isAdmin) {
            $url = route('manage.borrowings.show', ['organization' => $this->borrowing->organization->slug, 'borrowing' => $this->borrowing->id]);
            return (new MailMessage)
                        ->subject('Peringatan: Peminjaman Terlambat (Overdue)')
                        ->greeting("Halo {$notifiable->name}!")
                        ->line("Peminjaman aset {$this->borrowing->asset->name} oleh {$this->borrowing->user->name} telah melewati batas waktu pengembalian ({$this->borrowing->due_date->format('d M Y')}).")
                        ->action('Lihat Peminjaman', $url);
        }

        $url = route('my-borrowings.show', $this->borrowing->id);
        return (new MailMessage)
                    ->subject('Peringatan: Peminjaman Anda Terlambat (Overdue)')
                    ->greeting("Halo {$notifiable->name}!")
                    ->line("Peminjaman aset {$this->borrowing->asset->name} Anda telah melewati batas waktu pengembalian ({$this->borrowing->due_date->format('d M Y')}).")
                    ->line('Harap segera mengembalikan aset tersebut untuk menghindari denda lebih lanjut.')
                    ->action('Lihat Peminjaman', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->isAdmin) {
            return [
                'title' => 'Peminjaman Terlambat',
                'message' => "Peminjaman {$this->borrowing->asset->name} oleh {$this->borrowing->user->name} telah overdue.",
                'url' => route('manage.borrowings.show', ['organization' => $this->borrowing->organization->slug, 'borrowing' => $this->borrowing->id]),
                'category' => 'borrowing',
                'resource_id' => $this->borrowing->id,
                'organization_id' => $this->borrowing->organization_id,
            ];
        }

        return [
            'title' => 'Peminjaman Anda Terlambat',
            'message' => "Peminjaman {$this->borrowing->asset->name} Anda telah overdue. Segera kembalikan.",
            'url' => route('my-borrowings.show', $this->borrowing->id),
            'category' => 'borrowing',
            'resource_id' => $this->borrowing->id,
            'organization_id' => $this->borrowing->organization_id,
        ];
    }
}
