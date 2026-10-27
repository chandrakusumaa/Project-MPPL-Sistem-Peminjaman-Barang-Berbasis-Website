<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    public function __invoke(Request $request, Organization $organization, Asset $asset)
    {
        Gate::authorize('operate', $organization);
        abort_unless($asset->organization_id === $organization->id, 404);
        $format = $request->validate(['format' => 'nullable|in:svg,png,sticker'])['format'] ?? 'svg';
        abort_if($format === 'png' && ! extension_loaded('imagick'), 503, 'Format PNG memerlukan ekstensi imagick.');
        $svg = (string) QrCode::format('svg')->size(300)->generate(route('asset.show', [$organization, $asset]));
        if (! $organization->archived_at) {
            AssetLog::create(['organization_id' => $organization->id, 'asset_id' => $asset->id, 'user_id' => auth()->id(), 'event' => 'qr_generated', 'description' => 'QR aset dibuat']);
        }
        if ($format === 'sticker') {
            return view('qr-sticker', ['org' => $organization, 'asset' => $asset, 'svg' => $svg]);
        }
        $image = $format === 'png' ? (string) QrCode::format('png')->size(600)->generate(route('asset.show', [$organization, $asset])) : $svg;

        return response($image)->header('Content-Type', ($format === 'svg' ? 'image/svg+xml' : 'image/png'))->header('Content-Disposition', 'inline; filename="'.$asset->code.'.'.$format.'"');
    }
}
