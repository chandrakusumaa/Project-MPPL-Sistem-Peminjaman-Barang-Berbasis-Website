<?php

use App\Actions\Scan\ResolveScannedAsset;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public string $manualCode = '';
    public string $status = '';
    public string $message = '';
    public ?string $organizationSlug = null;

    public function processScan(ResolveScannedAsset $action, string $payload)
    {
        $this->reset(['status', 'message', 'organizationSlug']);

        try {
            $result = $action->execute($payload, auth()->user());
            
            if ($result['status'] === 'success') {
                $this->redirect($result['url'], navigate: true);
                return;
            }

            $this->status = $result['status'];
            $this->message = $result['message'];

            if ($this->status === 'not_member' && isset($result['organization'])) {
                $this->organizationSlug = $result['organization']->slug;
            }

        } catch (\Exception $e) {
            $this->status = 'invalid';
            $this->message = 'Gagal memproses kode QR: ' . $e->getMessage();
        }
    }

    public function processManual()
    {
        $this->validate([
            'manualCode' => 'required|string|max:255'
        ]);

        $this->processScan(app(ResolveScannedAsset::class), $this->manualCode);
    }
}; ?>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold mb-2">Scan QR Code Aset</h2>
                    <p class="text-gray-500 dark:text-gray-400">Pindai QR Code pada aset untuk melihat detail, meminjam, atau melaporkan kerusakan.</p>
                </div>

                @if($status === 'invalid')
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-center" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ $message }}</span>
                    </div>
                @elseif($status === 'not_member')
                    <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-5 rounded relative text-center" role="alert">
                        <x-flux::icon.exclamation-triangle class="size-10 mx-auto mb-2 text-yellow-600" />
                        <strong class="block font-bold text-lg mb-1">Akses Ditolak</strong>
                        <span class="block mb-4">{{ $message }}</span>
                        
                        @if($organizationSlug)
                            <a href="{{ route('explore.show', $organizationSlug) }}" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ajukan Bergabung
                            </a>
                        @endif
                    </div>
                @endif

                <div class="aspect-square max-w-sm mx-auto bg-black rounded-lg overflow-hidden relative shadow-inner mb-6" wire:ignore>
                    <div id="reader" class="w-full h-full"></div>
                    <div id="loading" class="absolute inset-0 flex items-center justify-center bg-gray-900 z-10">
                        <div class="text-white text-center">
                            <x-flux::icon.camera class="size-8 mx-auto mb-2 opacity-50 animate-pulse" />
                            <p class="text-sm">Menyiapkan kamera...</p>
                        </div>
                    </div>
                </div>

                <div class="max-w-sm mx-auto">
                    <div class="relative flex items-center py-5">
                        <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">ATAU</span>
                        <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                    </div>

                    <form wire:submit="processManual">
                        <x-input-label for="manualCode" value="Masukkan Kode Aset Manual" class="sr-only" />
                        <div class="flex gap-2">
                            <x-text-input wire:model="manualCode" id="manualCode" type="text" class="block w-full" placeholder="Masukkan ID/Kode Aset" required />
                            <x-primary-button>Cari</x-primary-button>
                        </div>
                        <x-input-error :messages="$errors->get('manualCode')" class="mt-2" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    const loadHtml5QrCode = async () => {
        // Kita bisa meload library dari CDN jika belum ada
        if (typeof Html5Qrcode === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode';
            script.async = true;
            document.head.appendChild(script);
            
            await new Promise((resolve) => {
                script.onload = resolve;
            });
        }
        
        const html5QrCode = new Html5Qrcode("reader");
        const loadingEl = document.getElementById('loading');
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        html5QrCode.start({ facingMode: "environment" }, config, (decodedText, decodedResult) => {
            // Berhenti scanning sementara saat memproses
            html5QrCode.pause();
            loadingEl.style.display = 'flex';
            loadingEl.querySelector('p').innerText = 'Memproses...';
            
            // Panggil method Livewire
            $wire.processScan(decodedText).then(() => {
                // Resume jika terjadi error dan tidak redirect
                html5QrCode.resume();
                loadingEl.style.display = 'none';
            });
        }, (errorMessage) => {
            // parse error, abaikan saja (frame tidak berisi qr)
        })
        .then(() => {
            loadingEl.style.display = 'none';
        })
        .catch((err) => {
            loadingEl.querySelector('p').innerHTML = `Gagal mengakses kamera.<br><span class="text-xs opacity-75 mt-1 block">Pastikan browser mengizinkan akses dan menggunakan HTTPS.</span>`;
            loadingEl.querySelector('svg').outerHTML = `<svg class="size-8 mx-auto mb-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
        });

        // Cleanup saat berpindah halaman
        document.addEventListener('livewire:navigating', () => {
            if (html5QrCode.isScanning) {
                html5QrCode.stop();
            }
        });
    };

    loadHtml5QrCode();
</script>
@endscript
