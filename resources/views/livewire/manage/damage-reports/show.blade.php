<?php

use App\Models\Organization;
use App\Models\DamageReport;
use App\Enums\DamageReportStatus;
use App\Enums\DamageSeverity;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.manage')] class extends Component {
    public Organization $organization;
    public DamageReport $damageReport;

    public $severity;
    public $resolution_notes;

    public function mount(Organization $organization, DamageReport $damageReport)
    {
        if ($damageReport->organization_id !== $organization->id) {
            abort(404);
        }

        $this->organization = $organization;
        $this->damageReport = $damageReport;

        $this->severity = $damageReport->severity?->value ?? '';
        $this->resolution_notes = $damageReport->resolution_notes ?? '';
    }

    public function assess(\App\Actions\Asset\AssessDamageReport $action)
    {
        $this->validate([
            'severity' => 'required|string',
            'resolution_notes' => 'nullable|string',
        ]);

        try {
            $this->damageReport = $action->execute($this->damageReport, auth()->user(), [
                'severity' => DamageSeverity::from($this->severity),
                'resolution_notes' => $this->resolution_notes,
            ]);
            Flux::toast('Detail kerusakan berhasil diperbarui.', variant: 'success');
        } catch (\Exception $e) {
            Flux::toast($e->getMessage(), variant: 'danger');
        }
    }

    public function startMaintenance(\App\Actions\Asset\StartMaintenance $action)
    {
        $this->validate([
            'severity' => 'required|string',
            'resolution_notes' => 'nullable|string',
        ]);

        try {
            $this->damageReport = $action->execute($this->damageReport, auth()->user(), [
                'severity' => DamageSeverity::from($this->severity),
                'resolution_notes' => $this->resolution_notes,
            ]);
            Flux::toast('Maintenance berhasil dimulai. Status aset kini Perbaikan.', variant: 'success');
        } catch (\Exception $e) {
            Flux::toast($e->getMessage(), variant: 'danger');
        }
    }

    public function markAsNoted(\App\Actions\Asset\AssessDamageReport $action)
    {
        $this->validate([
            'severity' => 'required|string',
            'resolution_notes' => 'nullable|string',
        ]);

        try {
            $this->damageReport = $action->execute($this->damageReport, auth()->user(), [
                'severity' => DamageSeverity::from($this->severity),
                'resolution_notes' => $this->resolution_notes,
            ]);
            
            // Mark as noted
            $this->damageReport->update([
                'status' => DamageReportStatus::NOTED,
                'handled_by' => auth()->id(),
            ]);

            Flux::toast('Laporan dicatat tanpa maintenance.', variant: 'success');
        } catch (\Exception $e) {
            Flux::toast($e->getMessage(), variant: 'danger');
        }
    }

    public function finishMaintenance(\App\Actions\Asset\FinishMaintenance $action)
    {
        $this->validate([
            'resolution_notes' => 'required|string',
        ]);

        try {
            $this->damageReport = $action->execute($this->damageReport, auth()->user(), [
                'resolution_notes' => $this->resolution_notes,
            ]);
            Flux::toast('Maintenance selesai. Status aset kini Tersedia.', variant: 'success');
        } catch (\Exception $e) {
            Flux::toast($e->getMessage(), variant: 'danger');
        }
    }
}; ?>


    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('manage.damage-reports.index', $organization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <x-flux::icon.arrow-left class="size-5" />
                </a>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Laporan Kerusakan #{{ $damageReport->id }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dilaporkan oleh {{ $damageReport->reporter->name }} pada {{ $damageReport->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
            <div>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $damageReport->status->color() }}-100 text-{{ $damageReport->status->color() }}-800">
                    {{ $damageReport->status->label() }}
                </span>
            </div>
        </div>



        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1 space-y-6">
                <!-- Info Aset -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Aset</h3>
                    
                    @if($damageReport->asset->photo)
                        <img src="{{ Storage::url($damageReport->asset->photo) }}" class="w-full rounded shadow-sm mb-4">
                    @else
                        <div class="w-full aspect-square bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center text-gray-400 mb-4">
                            <x-flux::icon.photo class="size-10" />
                        </div>
                    @endif

                    <div class="mb-2">
                        <span class="text-xs text-gray-500 block">Nama Aset</span>
                        <a href="{{ route('manage.inventory.show', ['organization' => $organization->slug, 'asset' => $damageReport->asset->code]) }}" class="font-medium text-indigo-600 hover:underline" target="_blank">{{ $damageReport->asset->name }}</a>
                    </div>
                    <div class="mb-2">
                        <span class="text-xs text-gray-500 block">Kode Aset</span>
                        <span class="text-sm">{{ $damageReport->asset->code }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Status Aset Saat Ini</span>
                        <span class="px-2 mt-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $damageReport->asset->status->value === 'available' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $damageReport->asset->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $damageReport->asset->status->value === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $damageReport->asset->status->value === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($damageReport->asset->status->value) }}
                        </span>
                    </div>
                </div>

                <!-- Info Pelapor -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Detail Laporan</h3>
                    
                    <div class="mb-4">
                        <span class="text-xs text-gray-500 block">Pelapor</span>
                        <span class="text-sm">{{ $damageReport->reporter->name }} ({{ $damageReport->reporter->email }})</span>
                    </div>

                    @if($damageReport->borrowing_id)
                        <div class="mb-4">
                            <span class="text-xs text-gray-500 block">Sumber</span>
                            <a href="{{ route('manage.borrowings.show', ['organization' => $organization->slug, 'borrowing' => $damageReport->borrowing_id]) }}" class="text-sm text-indigo-600 hover:underline" target="_blank">Inspeksi Return Peminjaman #{{ $damageReport->borrowing_id }}</a>
                        </div>
                    @endif

                    <div class="mb-4">
                        <span class="text-xs text-gray-500 block">Deskripsi Kerusakan dari Pelapor</span>
                        <p class="text-sm mt-1 bg-gray-50 dark:bg-gray-900 p-2 rounded border border-gray-200 dark:border-gray-700 whitespace-pre-line">{{ $damageReport->description }}</p>
                    </div>

                    @if($damageReport->photo)
                        <div>
                            <span class="text-xs text-gray-500 block mb-2">Foto Kerusakan</span>
                            <img src="{{ Storage::url($damageReport->photo) }}" class="w-full rounded shadow-sm">
                        </div>
                    @endif
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Penilaian & Tindakan</h3>
                    
                    <form class="space-y-6">
                        <div>
                            <div class="flex items-center gap-2">
                                <x-input-label for="severity" value="Tingkat Keparahan (Severity)" />
                                <flux:tooltip content="Dilaporkan member tanpa severity. Nilai di sini diisi oleh staff: Minor (tetap tersedia) atau Major (butuh maintenance).">
                                    <flux:icon.information-circle class="size-4 text-gray-400 cursor-help" />
                                </flux:tooltip>
                            </div>
                            <select wire:model="severity" id="severity" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" {{ in_array($damageReport->status->value, ['resolved', 'noted']) ? 'disabled' : '' }}>
                                <option value="">-- Belum Dinilai --</option>
                                @foreach(App\Enums\DamageSeverity::cases() as $sev)
                                    <option value="{{ $sev->value }}">{{ $sev->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('severity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="resolution_notes" value="Catatan Penyelesaian / Tindakan" />
                            <textarea wire:model="resolution_notes" id="resolution_notes" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" {{ in_array($damageReport->status->value, ['resolved', 'noted']) ? 'disabled' : '' }}></textarea>
                            <x-input-error :messages="$errors->get('resolution_notes')" class="mt-2" />
                        </div>

                        @if($damageReport->status->value === 'open')
                            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" wire:click="assess" class="inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    Simpan Detail
                                </button>

                                <button type="button" wire:click="markAsNoted" wire:confirm="Laporan akan dicatat (Noted) dan tidak ada maintenance. Aset tetap tersedia." class="inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none transition ease-in-out duration-150">
                                    Hanya Catat (Noted)
                                </button>

                                <button type="button" wire:click="startMaintenance" wire:confirm="Mulai maintenance? Status aset akan menjadi Maintenance dan tidak bisa dipinjam." class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none transition ease-in-out duration-150">
                                    Mulai Maintenance
                                </button>
                            </div>
                        @elseif($damageReport->status->value === 'in_maintenance')
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-yellow-600 dark:text-yellow-400 mb-4">Aset sedang dalam perbaikan. Isikan catatan penyelesaian sebelum mengklik Selesai.</p>
                                
                                <button type="button" wire:click="finishMaintenance" wire:confirm="Selesaikan maintenance? Status aset akan kembali Available." class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none transition ease-in-out duration-150">
                                    Selesai Perbaikan
                                </button>
                            </div>
                        @else
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500">Laporan telah ditutup ({{ $damageReport->status->label() }}) oleh {{ $damageReport->handler?->name ?? 'Sistem' }}.</p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

