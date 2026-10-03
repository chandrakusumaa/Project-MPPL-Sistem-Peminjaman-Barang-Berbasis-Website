<?php

namespace App\Actions\Asset;

use App\Enums\AssetStatus;
use App\Models\Asset;
use Exception;

class DeleteAsset
{
    public function execute(Asset $asset): void
    {
        if ($asset->status === AssetStatus::BORROWED) {
            throw new Exception('Aset tidak dapat dihapus karena sedang dipinjam.');
        }

        // Cek jika ada peminjaman aktif selain status aset (misalnya pending request)
        // Di rule: "Ditolak dengan pesan jelas jika status aset BORROWED atau punya peminjaman aktif."
        $activeBorrowingCount = $asset->borrowings()->whereIn('status', ['borrowed', 'overdue'])->count();
        if ($activeBorrowingCount > 0) {
            throw new Exception('Aset tidak dapat dihapus karena masih memiliki peminjaman aktif.');
        }

        $asset->delete();
    }
}
