<?php

use Livewire\Volt\Component;
use App\Models\Organization;
use App\Models\Asset;
use App\Models\Borrowing;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\layout;

layout('components.layouts.manage');

new class extends Component {
    public Organization $organization;
    public $assetStats;
    public $borrowingStats;
    public $topAssets;
    public $overdueBorrowings;
    public $pendingRequests;
    public $chartData = [];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        
        // Asset Stats
        $this->assetStats = Asset::where('organization_id', $organization->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            
        // Make sure all statuses have a default of 0 if not present
        $statuses = ['available', 'borrowed', 'maintenance', 'lost'];
        foreach ($statuses as $status) {
            $this->assetStats[$status] = $this->assetStats[$status] ?? 0;
        }
        $this->assetStats['total'] = array_sum($this->assetStats);

        // Borrowing Stats
        $this->borrowingStats = [
            'overdue' => Borrowing::where('organization_id', $organization->id)
                ->where('status', 'overdue')
                ->count(),
            'pending' => Borrowing::where('organization_id', $organization->id)
                ->where('status', 'pending')
                ->count(),
        ];

        // Top 5 Assets
        $this->topAssets = Asset::where('organization_id', $organization->id)
            ->withCount('borrowings')
            ->orderBy('borrowings_count', 'desc')
            ->take(5)
            ->get();

        // Lists
        $this->overdueBorrowings = Borrowing::where('organization_id', $organization->id)
            ->where('status', 'overdue')
            ->with(['user', 'asset'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $this->pendingRequests = Borrowing::where('organization_id', $organization->id)
            ->where('status', 'pending')
            ->with(['user', 'asset'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $this->prepareChartData();
    }
    
    private function prepareChartData()
    {
        // Bar chart: borrowings last 6 months
        $months = collect(range(5, 0))->map(function($i) {
            return now()->subMonths($i)->format('Y-m');
        });
        
        $monthlyBorrowingsRaw = Borrowing::where('organization_id', $this->organization->id)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get();
            
        $borrowingsPerMonth = $monthlyBorrowingsRaw->groupBy(function($item) {
            return $item->created_at->format('Y-m');
        })->map(function($group) {
            return $group->count();
        })->toArray();
            
        $monthlyData = $months->map(function($month) use ($borrowingsPerMonth) {
            return $borrowingsPerMonth[$month] ?? 0;
        })->toArray();
        
        $monthLabels = $months->map(function($month) {
            return \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y');
        })->toArray();

        $this->chartData = [
            'doughnut' => [
                'labels' => ['Tersedia', 'Dipinjam', 'Maintenance', 'Hilang'],
                'data' => [
                    $this->assetStats['available'],
                    $this->assetStats['borrowed'],
                    $this->assetStats['maintenance'],
                    $this->assetStats['lost']
                ],
                'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'] // emerald, blue, amber, red
            ],
            'bar' => [
                'labels' => $monthLabels,
                'data' => $monthlyData,
            ]
        ];
    }
}; ?>

<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Dashboard</h1>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Aset</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $assetStats['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Tersedia</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $assetStats['available'] }}</p>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Dipinjam</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $assetStats['borrowed'] }}</p>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Maintenance</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $assetStats['maintenance'] }}</p>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 relative overflow-hidden">
                    <div class="absolute inset-0 bg-red-50 dark:bg-red-900/10 z-0"></div>
                    <div class="relative z-10">
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">Hilang</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $assetStats['lost'] }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Overdue</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $borrowingStats['overdue'] }}</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Komposisi Aset</h3>
                    <div class="aspect-square relative w-full max-w-[250px] mx-auto">
                        <canvas id="assetDoughnutChart"></canvas>
                    </div>
                </div>
                
                <div class="lg:col-span-2 bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Aktivitas Peminjaman (6 Bulan)</h3>
                    <div class="relative w-full h-[250px]">
                        <canvas id="borrowingsBarChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top 5 Assets -->
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Top 5 Aset Dipinjam</h3>
                    <ul class="space-y-4">
                        @forelse($topAssets as $asset)
                            <li class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center shrink-0 overflow-hidden">
                                    @if($asset->photo)
                                        <img src="{{ Storage::url($asset->photo) }}" class="h-full w-full object-cover">
                                    @else
                                        <flux:icon.cube class="size-5 text-zinc-400" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $asset->name }}</p>
                                    <p class="text-xs text-zinc-500 truncate">{{ $asset->code }}</p>
                                </div>
                                <div class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ $asset->borrowings_count }}x
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-zinc-500">Belum ada data peminjaman.</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Overdue & Pending -->
                <div class="lg:col-span-2 space-y-6">
                    @if($borrowingStats['pending'] > 0)
                        <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-amber-200 dark:border-amber-900/50">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-amber-600 dark:text-amber-500">Pending Request Terbaru</h3>
                                <a href="{{ route('manage.borrowings.index', $organization->slug) }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">Lihat Semua</a>
                            </div>
                            <div class="space-y-3">
                                @foreach($pendingRequests as $req)
                                    <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $req->user->name }} <span class="text-zinc-500 font-normal">ingin meminjam</span> {{ $req->asset->name }}</p>
                                            <p class="text-xs text-zinc-500 mt-0.5">{{ $req->borrow_date->format('d M') }} - {{ $req->due_date->format('d M Y') }}</p>
                                        </div>
                                        <a href="{{ route('manage.borrowings.show', [$organization->slug, $req->id]) }}" wire:navigate class="text-xs font-medium bg-white dark:bg-zinc-800 px-3 py-1.5 rounded shadow-sm border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50">Tinjau</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-red-200 dark:border-red-900/50">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-red-600 dark:text-red-500">Jatuh Tempo (Overdue)</h3>
                            <a href="{{ route('manage.borrowings.index', $organization->slug) }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">Lihat Semua</a>
                        </div>
                        
                        @if($overdueBorrowings->isEmpty())
                            <p class="text-sm text-zinc-500">Tidak ada peminjaman yang terlambat.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($overdueBorrowings as $overdue)
                                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $overdue->asset->name }}</p>
                                            <p class="text-xs text-zinc-500 mt-0.5">Peminjam: {{ $overdue->user->name }} &bull; Jatuh tempo: {{ $overdue->due_date->format('d M Y') }}</p>
                                        </div>
                                        <a href="{{ route('manage.borrowings.show', [$organization->slug, $overdue->id]) }}" wire:navigate class="text-xs font-medium bg-white dark:bg-zinc-800 px-3 py-1.5 rounded shadow-sm border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50">Detail</a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chartData = @json($chartData);
            
            // Doughnut Chart
            const ctxDoughnut = document.getElementById('assetDoughnutChart');
            if (ctxDoughnut) {
                new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.doughnut.labels,
                        datasets: [{
                            data: chartData.doughnut.data,
                            backgroundColor: chartData.doughnut.colors,
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
            
            // Bar Chart
            const ctxBar = document.getElementById('borrowingsBarChart');
            if (ctxBar) {
                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: chartData.bar.labels,
                        datasets: [{
                            label: 'Peminjaman Baru',
                            data: chartData.bar.data,
                            backgroundColor: '#6366f1',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                                }
                            },
                            x: {
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endscript
</div>
