<?php

use App\Models\Organization;
use App\Models\DamageReport;
use App\Enums\DamageReportStatus;
use App\Enums\DamageSeverity;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Organization $organization;

    public string $search = '';
    public string $statusFilter = '';
    public string $severityFilter = '';

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSeverityFilter()
    {
        $this->resetPage();
    }

    public function getReportsProperty()
    {
        return DamageReport::where('organization_id', $this->organization->id)
            ->with(['asset', 'reporter', 'handler'])
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->severityFilter, function ($query) {
                $query->where('severity', $this->severityFilter);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('asset', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                })->orWhereHas('reporter', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(15);
    }
}; ?>

<x-layouts.manage>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Laporan Kerusakan</h2>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full" placeholder="Cari aset atau pelapor..." />
                </div>
                <div class="sm:w-48">
                    <select wire:model.live="statusFilter" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Semua Status</option>
                        @foreach(App\Enums\DamageReportStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:w-48">
                    <select wire:model.live="severityFilter" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Semua Severity</option>
                        @foreach(App\Enums\DamageSeverity::cases() as $severity)
                            <option value="{{ $severity->value }}">{{ $severity->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tgl Laporan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pelapor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Severity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->reports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $report->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $report->asset->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $report->asset->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $report->reporter->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($report->borrowing_id)
                                            <span class="text-indigo-500">Dari Inspeksi Return</span>
                                        @else
                                            Laporan Manual
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->severity)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $report->severity->color() }}-100 text-{{ $report->severity->color() }}-800">
                                            {{ $report->severity->label() }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-xs italic">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $report->status->color() }}-100 text-{{ $report->status->color() }}-800">
                                        {{ $report->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('manage.damage-reports.show', ['organization' => $organization->slug, 'damageReport' => $report->id]) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300" wire:navigate>Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Belum ada laporan kerusakan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $this->reports->links() }}
            </div>
        </div>
    </div>
</x-layouts.manage>
