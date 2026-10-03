<?php

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleChangedOrRemovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization, 
        public string $actionType, 
        public ?string $newRole = null
    ) {
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
        $url = route('organizations.index');
        
        $message = (new MailMessage)->greeting("Halo {$notifiable->name}!");

        if ($this->actionType === 'removed') {
            $message->subject('Keanggotaan Anda Dihapus')
                    ->line("Keanggotaan Anda di organisasi {$this->organization->name} telah dihapus oleh Admin.");
        } else {
            $message->subject('Perubahan Role Keanggotaan')
                    ->line("Role Anda di organisasi {$this->organization->name} telah diubah menjadi: " . ucfirst($this->newRole) . ".");
        }

        return $message->action('Buka Aplikasi', $url)
                       ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->actionType === 'removed') {
            return [
                'title' => 'Dikeluarkan dari Organisasi',
                'message' => "Anda telah dikeluarkan dari {$this->organization->name}.",
                'url' => route('organizations.index'),
                'category' => 'membership',
                'resource_id' => null,
                'organization_id' => $this->organization->id,
            ];
        }

        return [
            'title' => 'Perubahan Role',
            'message' => "Role Anda di {$this->organization->name} menjadi " . ucfirst($this->newRole) . ".",
            'url' => route('organizations.index'),
            'category' => 'membership',
            'resource_id' => null,
            'organization_id' => $this->organization->id,
        ];
    }
}
