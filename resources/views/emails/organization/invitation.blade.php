<x-mail::message>
# Undangan Bergabung

Halo,

Anda telah diundang oleh **{{ $inviterName }}** untuk bergabung dengan organisasi **{{ $organizationName }}** sebagai **{{ ucfirst($role) }}**.

Silakan klik tombol di bawah ini untuk menerima undangan:

<x-mail::button :url="$acceptUrl">
Terima Undangan
</x-mail::button>

Undangan ini akan kedaluwarsa dalam 7 hari.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
