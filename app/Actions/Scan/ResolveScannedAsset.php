<?php

namespace App\Actions\Scan;

use App\Models\Asset;
use App\Models\Organization;
use App\Models\User;

class ResolveScannedAsset
{
    /**
     * @return array{status: string, message?: string, asset?: Asset, url?: string}
     */
    public function execute(string $qrPayload, User $user): array
    {
        // QR payload expected to be absolute URL to: /o/{slug}/assets/{code}
        // Let's parse the URL or try to extract {code}
        $parsedUrl = parse_url($qrPayload);
        if (! $parsedUrl || ! isset($parsedUrl['path'])) {
            return ['status' => 'invalid', 'message' => 'Format QR Code tidak dikenali.'];
        }

        $pathSegments = explode('/', trim($parsedUrl['path'], '/'));

        // Expected path: o/{slug}/assets/{code}
        if (count($pathSegments) < 4 || $pathSegments[0] !== 'o' || $pathSegments[2] !== 'assets') {
            // Also try just parsing code directly if the user scans a raw code by mistake
            $code = basename($parsedUrl['path']);
            $asset = Asset::where('code', $code)->first();
            if (! $asset) {
                return ['status' => 'invalid', 'message' => 'QR / Aset Invalid.'];
            }
        } else {
            $slug = $pathSegments[1];
            $code = $pathSegments[3];

            $organization = Organization::where('slug', $slug)->first();
            if (! $organization) {
                return ['status' => 'invalid', 'message' => 'QR / Aset Invalid.'];
            }

            $asset = Asset::where('code', $code)->where('organization_id', $organization->id)->first();
            if (! $asset) {
                return ['status' => 'invalid', 'message' => 'QR / Aset Invalid.'];
            }
        }

        // Cek apakah user adalah anggota
        if (! $user->isMemberOf($asset->organization)) {
            return [
                'status' => 'not_member',
                'message' => 'Anda harus bergabung ke organisasi '.$asset->organization->name.' terlebih dahulu.',
                'organization' => $asset->organization,
            ];
        }

        return [
            'status' => 'success',
            'asset' => $asset,
            'url' => route('organization.asset.show', ['organization' => $asset->organization->slug, 'code' => $asset->code]),
        ];
    }
}
