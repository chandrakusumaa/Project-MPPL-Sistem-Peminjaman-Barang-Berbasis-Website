<?php

namespace App\Actions\Borrowing;

use App\Enums\AssetStatus;
use App\Enums\BorrowingStatus;
use App\Events\BorrowingApproved;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ApproveBorrowing
{
    public function execute(Borrowing $borrowing, User $approver): Borrowing
    {
        if ($borrowing->status !== BorrowingStatus::PENDING) {
            throw new Exception('Hanya peminjaman berstatus pending yang dapat disetujui.');
        }

        return DB::transaction(function () use ($borrowing, $approver) {
            // Lock row aset
            $asset = Asset::where('id', $borrowing->asset_id)->lockForUpdate()->first();

            if ($asset->status !== AssetStatus::AVAILABLE) {
                throw new Exception('Aset sedang tidak tersedia.');
            }

            // Update aset
            $asset->update(['status' => AssetStatus::BORROWED]);

            // Catat log aset
            $asset->logs()->create([
                'organization_id' => $asset->organization_id,
                'user_id' => $approver->id,
                'event' => 'borrowed',
                'description' => 'Aset dipinjamkan kepada '.$borrowing->user->name,
                'metadata' => ['borrowing_id' => $borrowing->id],
            ]);

            // Update peminjaman
            $borrowing->update([
                'status' => BorrowingStatus::BORROWED,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            event(new BorrowingApproved($borrowing));

            return $borrowing;
        });
    }
}
