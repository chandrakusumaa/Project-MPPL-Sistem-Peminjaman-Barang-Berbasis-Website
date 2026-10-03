<?php

namespace App\Notifications;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public OrganizationInvitation $invitation)
    {
    }

    public function via(object $notifiable): array
    {
        // Email transaksional (undangan) selalu terkirim
        $channels = ['mail'];

        // Jika notifiable adalah User, tambahkan notifikasi in-app
        if ($notifiable instanceof \App\Models\User) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invitations.accept', $this->invitation->token);
        
        return (new MailMessage)
                    ->subject('Undangan Bergabung ke Organisasi')
                    ->line("Anda telah diundang untuk bergabung ke organisasi {$this->invitation->organization->name} sebagai {$this->invitation->role->label()}.")
                    ->action('Terima Undangan', $url)
                    ->line('Tautan ini berlaku selama 7 hari.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Undangan Bergabung',
            'message' => "Anda telah diundang untuk bergabung ke organisasi {$this->invitation->organization->name} sebagai {$this->invitation->role->label()}.",
            'url' => route('invitations.accept', $this->invitation->token),
            'category' => 'membership',
            'resource_id' => $this->invitation->id,
            'organization_id' => $this->invitation->organization_id,
        ];
    }
}
