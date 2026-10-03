<?php

use App\Actions\Asset\DeleteAsset;
use App\Models\Asset;
use App\Models\Organization;
use Livewire\Volt\Component;

new class extends Component {
    public Organization $organization;
    public Asset $asset;
    public string $tab = 'detail'; // detail, qr, history

    public function mount(Organization $organization, Asset $asset)
    {
        if ($asset->organization_id !== $organization->id) {
            abort(404);
        }

        $this->organization = $organization;
        $this->asset = $asset;
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function getAssetLogsProperty()
    {
        return $this->asset->logs()->with('user')->latest()->get();
    }

    public function getQrUrlProperty()
    {
        return route('organization.asset.show', ['organization' => $this->organization->slug, 'asset' => $this->asset->code]);
    }

    public function delete(DeleteAsset $action)
    {
        if ($this->organization->archived_at) {
            session()->flash('error', 'Organisasi diarsipkan, tidak dapat menghapus aset.');
            return;
        }

        try {
            $action->execute($this->asset);
            session()->flash('success', 'Aset berhasil dihapus.');
            $this->redirectRoute('manage.inventory.index', ['organization' => $this->organization->slug], navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}; ?>

<x-layouts.manage>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('manage.inventory.index', $organization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <x-flux::icon.arrow-left class="size-5" />
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $asset->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $asset->code }}</p>
            </div>
            <div class="ml-auto flex gap-2">
                @if(!$organization->archived_at)
                    <a href="{{ route('manage.inventory.edit', ['organization' => $organization->slug, 'asset' => $asset->code]) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Edit Aset
                    </a>
                    <button wire:click="delete" wire:confirm="Yakin ingin menghapus aset ini? Operasi tidak dapat dibatalkan jika belum ada transaksi." class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Hapus
                    </button>
                @endif
            </div>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <button wire:click="setTab('detail')" class="{{ $tab === 'detail' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Detail Aset
                    </button>
                    <button wire:click="setTab('qr')" class="{{ $tab === 'qr' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Generate QR Code
                    </button>
                    <button wire:click="setTab('history')" class="{{ $tab === 'history' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        History
                    </button>
                </nav>
            </div>

            <div class="p-6">
                @if($tab === 'detail')
                    <div class="md:flex gap-8">
                        <div class="md:w-1/3 mb-6 md:mb-0">
                            @if($asset->photo)
                                <img src="{{ Storage::url($asset->photo) }}" class="w-full rounded-lg shadow-sm" alt="{{ $asset->name }}">
                            @else
                                <div class="w-full aspect-square bg-gray-100 dark:bg-gray-900 rounded-lg flex items-center justify-center text-gray-400">
                                    <x-flux::icon.photo class="size-20" />
                                </div>
                            @endif
                            <div class="mt-4 text-center">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    {{ $asset->status->value === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $asset->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $asset->status->value === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $asset->status->value === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    Status: {{ ucfirst($asset->status->value) }}
                                </span>
                            </div>
                        </div>
                        <div class="md:w-2/3">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $asset->category->name }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lokasi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $asset->location }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pembelian</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 nl2br">{{ $asset->description }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Spesifikasi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 nl2br">{{ $asset->specifications }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                @elseif($tab === 'qr')
                    <div class="text-center py-8">
                        <div class="inline-block p-4 bg-white rounded-lg shadow-sm mb-6 border border-gray-200">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($this->qrUrl) !!}
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                            Tempelkan stiker QR Code ini pada aset fisik. Anggota dapat memindai QR Code ini untuk melihat detail atau meminjam aset.
                        </p>
                        <div class="flex justify-center gap-4">
                            <a href="{{ route('manage.inventory.print', ['organization' => $organization->slug, 'asset' => $asset->code]) }}" target="_blank" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <x-flux::icon.printer class="size-4 mr-2" />
                                Print Stiker
                            </a>
                            <a href="data:image/svg+xml;base64,{!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(500)->generate($this->qrUrl)) !!}" download="{{ $asset->code }}.svg" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Download SVG
                            </a>
                            @if (extension_loaded('imagick'))
                                <a href="data:image/png;base64,{!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(500)->generate($this->qrUrl)) !!}" download="{{ $asset->code }}.png" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Download PNG
                                </a>
                            @endif
                        </div>
                    </div>

                @elseif($tab === 'history')
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($this->assetLogs as $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                                    {{ $log->event === 'created' ? 'bg-green-500' : 'bg-blue-500' }}
                                                ">
                                                    @if($log->event === 'created')
                                                        <x-flux::icon.plus class="size-4 text-white" />
                                                    @else
                                                        <x-flux::icon.pencil class="size-4 text-white" />
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $log->description }} <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Oleh: {{ $log->user->name ?? 'Sistem' }}</a></p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    <time datetime="{{ $log->created_at }}">{{ $log->created_at->format('d M Y H:i') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center text-gray-500 py-4">Belum ada riwayat.</div>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.manage>
