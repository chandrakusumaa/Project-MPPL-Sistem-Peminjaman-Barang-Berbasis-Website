<?php

namespace App\Notifications;

use App\Models\DamageReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DamageReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DamageReport $report) {}

    /**
     * Notifikasi laporan kerusakan bersifat transaksional: preferensi user
     * diabaikan, in-app dan email selalu dikirim.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('manage.damage-reports.show', ['organization' => $this->report->organization->slug, 'damageReport' => $this->report->id]);

        return (new MailMessage)
            ->subject('Laporan Kerusakan Baru')
            ->greeting("Halo {$notifiable->name}!")
            ->line("Pengguna {$this->report->reporter->name} telah melaporkan kerusakan pada aset {$this->report->asset->name}.")
            ->action('Lihat Laporan', $url)
            ->line('Silakan tinjau laporan ini dan ambil tindakan yang diperlukan.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Laporan Kerusakan',
            'message' => "Kerusakan aset {$this->report->asset->name} dilaporkan oleh {$this->report->reporter->name}.",
            'url' => route('manage.damage-reports.show', ['organization' => $this->report->organization->slug, 'damageReport' => $this->report->id]),
            'category' => 'damage',
            'resource_id' => $this->report->id,
            'organization_id' => $this->report->organization_id,
        ];
    }
}
