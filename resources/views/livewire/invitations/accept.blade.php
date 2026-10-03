<?php

use App\Actions\Membership\AcceptInvitation;
use App\Models\OrganizationInvitation;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $token;
    public ?OrganizationInvitation $invitation = null;
    public string $status = 'valid'; // valid, expired, invalid, wrong_email, already_member, accepted

    public function mount(string $token)
    {
        $this->token = $token;
        $this->invitation = OrganizationInvitation::where('token', $token)->first();

        if (!$this->invitation) {
            $this->status = 'invalid';
            return;
        }

        if ($this->invitation->status === 'accepted') {
            $this->status = 'accepted';
            return;
        }

        if ($this->invitation->status !== 'pending' || $this->invitation->expires_at->isPast()) {
            $this->status = 'expired';
            return;
        }

        if (!Auth::check()) {
            session()->put('url.intended', url()->current());
            $this->redirect(route('login', ['email' => $this->invitation->email]));
            return;
        }

        if (Auth::user()->email !== $this->invitation->email) {
            $this->status = 'wrong_email';
            return;
        }

        if (Auth::user()->isMemberOf($this->invitation->organization)) {
            $this->status = 'already_member';
            // Auto accept if not yet marked
            if ($this->invitation->status === 'pending') {
                $this->invitation->update(['status' => 'accepted']);
            }
            return;
        }
    }

    public function accept(AcceptInvitation $action)
    {
        if ($this->status !== 'valid' || !$this->invitation) {
            return;
        }

        try {
            $action->execute($this->invitation, Auth::user());
            $this->status = 'accepted';
            session()->flash('success', 'Berhasil bergabung dengan ' . $this->invitation->organization->name);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menerima undangan: ' . $e->getMessage());
        }
    }
}; ?>

<x-layouts.app>
    <div class="max-w-2xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                @if($status === 'invalid' || $status === 'expired')
                    <h2 class="text-2xl font-semibold mb-4 text-red-600">Undangan Tidak Valid</h2>
                    <p class="mb-6">Undangan ini tidak ditemukan, sudah ditarik, atau sudah kedaluwarsa.</p>
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Kembali ke Dashboard</a>
                
                @elseif($status === 'wrong_email')
                    <h2 class="text-2xl font-semibold mb-4 text-red-600">Email Tidak Sesuai</h2>
                    <p class="mb-6">Undangan ini ditujukan untuk email <strong>{{ $invitation->email }}</strong>, tetapi Anda login sebagai <strong>{{ Auth::user()->email }}</strong>.</p>
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Kembali ke Dashboard</a>
                
                @elseif($status === 'already_member')
                    <h2 class="text-2xl font-semibold mb-4 text-green-600">Anda Sudah Bergabung</h2>
                    <p class="mb-6">Anda sudah menjadi anggota di organisasi <strong>{{ $invitation->organization->name }}</strong>.</p>
                    <a href="{{ route('organization.catalog', $invitation->organization->slug) }}" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Buka Organisasi</a>
                
                @elseif($status === 'accepted')
                    <h2 class="text-2xl font-semibold mb-4 text-green-600">Undangan Telah Diterima</h2>
                    <p class="mb-6">Selamat! Anda telah bergabung dengan <strong>{{ $invitation->organization->name }}</strong> sebagai {{ ucfirst($invitation->role) }}.</p>
                    <a href="{{ route('organization.catalog', $invitation->organization->slug) }}" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Buka Organisasi</a>
                
                @elseif($status === 'valid')
                    <h2 class="text-2xl font-semibold mb-4">Undangan Bergabung</h2>
                    <p class="mb-6">Anda diundang untuk bergabung dengan organisasi <strong>{{ $invitation->organization->name }}</strong> sebagai <strong>{{ ucfirst($invitation->role) }}</strong>.</p>
                    
                    @if (session()->has('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-left" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Tolak
                        </a>
                        <button wire:click="accept" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Terima Undangan
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
