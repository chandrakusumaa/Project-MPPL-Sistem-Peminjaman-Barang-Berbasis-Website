<?php

namespace App\Notifications;

use App\Models\MembershipRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinRequestSubmittedNotification extends Notification implements ShouldQueue
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
        $url = route('manage.members.index', $this->request->organization->slug);

        return (new MailMessage)
                    ->subject('Permintaan Bergabung Baru')
                    ->greeting("Halo {$notifiable->name}!")
                    ->line("Pengguna {$this->request->user->name} telah mengajukan permintaan untuk bergabung ke organisasi {$this->request->organization->name}.")
                    ->line("Pesan: " . ($this->request->message ?: '-'))
                    ->action('Lihat Permintaan', $url)
                    ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Permintaan Bergabung Baru',
            'message' => "Pengguna {$this->request->user->name} ingin bergabung ke organisasi.",
            'url' => route('manage.members.index', $this->request->organization->slug),
            'category' => 'membership',
            'resource_id' => $this->request->id,
            'organization_id' => $this->request->organization_id,
        ];
    }
}
