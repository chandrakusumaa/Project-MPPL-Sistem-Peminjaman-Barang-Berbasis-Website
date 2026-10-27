<?php

namespace App\Livewire;

use App\Actions\DeleteAccount;
use App\Mail\EmailChangeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $avatarUpload;

    public string $name = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $email = '';

    public string $deletePassword = '';

    public function emailChange(): void
    {
        $this->validate(['email' => 'required|email|max:254|unique:users,email']);
        $u = auth()->user();
        $u->forceFill(['pending_email' => strtolower(trim($this->email)), 'pending_email_requested_at' => now()])->save();
        $link = URL::temporarySignedRoute('profile.email.verify', now()->addHour(), ['user' => $u->id, 'hash' => hash('sha256', $u->pending_email), 'version' => $u->pending_email_requested_at->getTimestamp()]);
        Mail::to($u->pending_email)->queue(new EmailChangeMail($link));
        session()->flash('status', 'Verifikasi email baru telah dikirim. Email lama tetap aktif sampai konfirmasi.');
    }

    public function deleteAccount()
    {
        $this->validate(['deletePassword' => 'required|current_password']);
        app(DeleteAccount::class)->execute(auth()->user());
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public bool $membership = true;

    public bool $borrowing = true;

    public bool $damage = true;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        foreach (['membership', 'borrowing', 'damage'] as $key) {
            $this->$key = auth()->user()->notification_preferences[$key] ?? true;
        }
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:150', 'avatarUpload' => 'nullable|image|mimes:jpg,jpeg,png|max:2048']);
        $u = auth()->user();
        $u->name = $this->name;
        if ($this->avatarUpload) {
            $u->avatar = $this->avatarUpload->store('avatars', 'public');
        }
        $u->notification_preferences = ['membership' => $this->membership, 'borrowing' => $this->borrowing, 'damage' => $this->damage];
        $u->save();
        session()->flash('status', 'Profil disimpan.');
    }

    public function changePassword(): void
    {
        $this->validate(['currentPassword' => 'required|current_password', 'password' => 'required|min:8|confirmed']);
        auth()->user()->update(['password' => $this->password]);
        $this->reset('password', 'password_confirmation', 'currentPassword');
        session()->flash('status', 'Kata sandi diperbarui.');
    }

    public function render()
    {
        return view('livewire.profile')->layout('components.layout');
    }
}
