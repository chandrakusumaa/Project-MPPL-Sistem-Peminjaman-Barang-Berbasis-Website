<?php

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    protected function rules(): array
    {
        return (new ForgotPasswordRequest)->rules();
    }

    protected function messages(): array
    {
        return (new ForgotPasswordRequest)->messages();
    }

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak permintaan. Silakan coba lagi dalam ' . ceil($seconds / 60) . ' menit.',
            ]);
        }
        RateLimiter::hit($throttleKey);

        $status = Password::sendResetLink($this->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', 'Link reset password telah dikirim ke email Anda.');
            $this->email = '';
        } else {
            throw ValidationException::withMessages([
                'email' => 'Email tidak ditemukan.',
            ]);
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Forgot password" description="Enter your email to receive a password reset link" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" label="{{ __('Email Address') }}" type="email" name="email" required autofocus placeholder="email@example.com" />
        </div>

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Email password reset link') }}</flux:button>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-400">
        Or, return to
        <x-text-link href="{{ route('login') }}">log in</x-text-link>
    </div>
</div>
