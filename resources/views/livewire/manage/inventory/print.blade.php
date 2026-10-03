<?php

use App\Models\Asset;
use App\Models\Organization;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.public')] class extends Component {
    public Organization $organization;
    public Asset $asset;

    public function mount(Organization $organization, Asset $asset)
    {
        if ($asset->organization_id !== $organization->id) {
            abort(404);
        }

        $this->organization = $organization;
        $this->asset = $asset;
    }

    public function getQrUrlProperty()
    {
        return route('organization.asset.show', ['organization' => $this->organization->slug, 'asset' => $this->asset->code]);
    }
}; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white border-2 border-black p-4 inline-block print:border-none print:shadow-none print:m-0 print:p-0">
        <div class="text-center w-[250px]">
            <h1 class="font-bold text-lg mb-1">{{ $organization->name }}</h1>
            <p class="text-sm font-semibold mb-2 truncate">{{ $asset->name }}</p>
            
            <div class="mx-auto flex justify-center mb-2">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($this->qrUrl) !!}
            </div>
            
            <p class="text-xs text-gray-600 font-mono">{{ $asset->code }}</p>
            <p class="text-[10px] text-gray-400 mt-1">Scan for details</p>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    }
</script>

<style>
    @media print {
        body {
            background-color: white !important;
        }
        @page { margin: 0; }
        nav, header, footer { display: none !important; }
    }
</style>
