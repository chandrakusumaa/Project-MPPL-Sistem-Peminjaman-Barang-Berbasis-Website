<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar;
    
    // Notification preferences
    public bool $notify_membership = true;
    public bool $notify_borrowing = true;
    public bool $notify_damage = true;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        
        $prefs = $user->notification_preferences;
        $prefs = is_array($prefs) ? $prefs : [];
        $this->notify_membership = (bool) ($prefs['membership'] ?? true);
        $this->notify_borrowing = (bool) ($prefs['borrowing'] ?? true);
        // Email laporan kerusakan bersifat transaksional: selalu aktif.
        $this->notify_damage = true;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'notify_membership' => 'boolean',
            'notify_borrowing' => 'boolean',
        ]);

        // Validasi skema preferensi (aturan dari Form Request); damage dipaksa true.
        $preferences = \App\Http\Requests\Settings\UpdateNotificationPreferencesRequest::validatePreferences([
            'membership' => $this->notify_membership,
            'borrowing' => $this->notify_borrowing,
            'damage' => true,
        ]);
        $this->notify_damage = true;

        $user->name = $validated['name'];
        
        if ($this->avatar) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        // Notification preferences
        $user->notification_preferences = $preferences;

        // Handle Email
        if ($validated['email'] !== $user->email) {
            $user->pending_email = $validated['email'];
            
            // Send signed URL to the new email
            $verificationUrl = URL::temporarySignedRoute(
                'profile.verify-pending-email',
                now()->addMinutes(60),
                ['user' => $user->id, 'email' => $user->pending_email]
            );
            
            // Since we don't have Mail setup properly to send to the unverified new email natively in Laravel's MustVerifyEmail,
            // we use our custom Notification. We send it directly via Notification facade route.
            \Illuminate\Support\Facades\Notification::route('mail', $user->pending_email)
                ->notify(new \App\Notifications\VerifyPendingEmail($verificationUrl));
                
            session()->flash('status', 'verification-link-sent');
        } else {
            // If they changed it back to current email
            $user->pending_email = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
        Flux::toast('Profil berhasil diperbarui.', variant: 'success');
    }
    
    public function cancelPendingEmail(): void
    {
        $user = Auth::user();
        $user->pending_email = null;
        $user->save();
        
        $this->email = $user->email;
        Flux::toast('Permintaan perubahan email dibatalkan.', variant: 'success');
    }

}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Profil" subheading="Perbarui informasi profil dan preferensi notifikasi Anda">
        @if (session('success'))
            <div class="mb-4 bg-green-50 text-green-700 p-3 rounded-lg text-sm border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg text-sm border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-8">
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Foto Profil</label>
                    <div class="flex items-center gap-4">
                        <div class="shrink-0 h-16 w-16 rounded-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-full w-full object-cover">
                            @elseif(auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" class="h-full w-full object-cover">
                            @else
                                <flux:icon.user class="size-8 text-zinc-400" />
                            @endif
                        </div>
                        <flux:input type="file" wire:model="avatar" accept="image/*" />
                    </div>
                </div>

                <flux:input wire:model="name" label="Nama Lengkap" type="text" name="name" required autofocus autocomplete="name" />

                <div>
                    <flux:input wire:model="email" label="Alamat Email" type="email" name="email" required autocomplete="email" />

                    @if (auth()->user()->pending_email)
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/50 rounded-lg">
                            <p class="text-sm text-amber-800 dark:text-amber-500">
                                Menunggu verifikasi untuk email baru: <strong>{{ auth()->user()->pending_email }}</strong>
                            </p>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1 mb-2">
                                Silakan periksa kotak masuk email tersebut. Email Anda saat ini ({{ auth()->user()->email }}) tetap berlaku hingga verifikasi selesai.
                            </p>
                            
                            <button
                                wire:click.prevent="cancelPendingEmail"
                                class="text-xs font-medium text-amber-700 hover:text-amber-900 dark:text-amber-500 dark:hover:text-amber-400 underline"
                            >
                                Batalkan perubahan email
                            </button>
                        </div>
                    @endif

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            Tautan verifikasi telah dikirim ke alamat email baru Anda.
                        </p>
                    @endif
                </div>
            </div>
            
            <hr class="border-zinc-200 dark:border-zinc-700">
            
            <div>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Preferensi Notifikasi Email</h3>
                <p class="text-sm text-zinc-500 mb-4">Pilih kategori notifikasi apa saja yang ingin Anda terima melalui email.</p>
                
                <div class="space-y-3">
                    <flux:checkbox wire:model="notify_membership" label="Keanggotaan Organisasi (Request & Perubahan Role)" />
                    <flux:checkbox wire:model="notify_borrowing" label="Peminjaman (Persetujuan, Overdue, & Pengembalian)" />
                    <div>
                        <flux:checkbox checked disabled label="Laporan Kerusakan & Maintenance Aset (wajib)" />
                        <p class="mt-1 ml-7 text-xs text-zinc-500">Email laporan kerusakan bersifat transaksional dan tidak dapat dimatikan.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">Simpan Perubahan</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    Tersimpan.
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
