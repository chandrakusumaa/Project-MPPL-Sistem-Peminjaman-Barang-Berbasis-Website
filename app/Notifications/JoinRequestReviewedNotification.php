<?php

namespace App\Notifications;

use App\Models\MembershipRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinRequestReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MembershipRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailFor('membership')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusStr = $this->request->status === 'approved' ? 'disetujui' : 'ditolak';
        $url = route('organizations.index');

        return (new MailMessage)
                    ->subject("Permintaan Bergabung Anda $statusStr")
                    ->greeting("Halo {$notifiable->name}!")
                    ->line("Permintaan Anda untuk bergabung ke organisasi {$this->request->organization->name} telah $statusStr.")
                    ->action('Buka Aplikasi', $url)
                    ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toDatabase(object $notifiable): array
    {
        $statusStr = $this->request->status === 'approved' ? 'disetujui' : 'ditolak';

        return [
            'title' => 'Permintaan Bergabung ' . ucfirst($statusStr),
            'message' => "Permintaan Anda ke organisasi {$this->request->organization->name} telah $statusStr.",
            'url' => route('organizations.index'),
            'category' => 'membership',
            'resource_id' => $this->request->id,
            'organization_id' => $this->request->organization_id,
        ];
    }
}
