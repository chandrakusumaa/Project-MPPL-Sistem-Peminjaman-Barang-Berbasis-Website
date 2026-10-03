<?php

namespace App\Actions\Borrowing;

use App\Enums\AssetStatus;
use App\Enums\BorrowingStatus;
use App\Enums\DamageReportStatus;
use App\Enums\DamageSeverity;
use App\Enums\ReturnCondition;
use App\Events\BorrowingReturned;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\DamageReport;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ProcessReturn
{
    public function execute(Borrowing $borrowing, User $processor, array $data): Borrowing
    {
        if (! in_array($borrowing->status, [BorrowingStatus::BORROWED, BorrowingStatus::OVERDUE])) {
            throw new Exception('Peminjaman tidak dalam status yang dapat dikembalikan.');
        }

        return DB::transaction(function () use ($borrowing, $processor, $data) {
            $asset = Asset::where('id', $borrowing->asset_id)->lockForUpdate()->first();

            $condition = ReturnCondition::from($data['return_condition']);
            $returnDate = isset($data['returned_at']) ? Carbon::parse($data['returned_at']) : now();
            $notes = $data['return_notes'] ?? null;
            $fineAmount = $data['fine_amount'] ?? 0;

            if ($condition !== ReturnCondition::GOOD && empty(trim($notes))) {
                throw new Exception('Catatan pengembalian wajib diisi jika kondisi aset tidak baik.');
            }

            // Update Borrowing
            $borrowing->update([
                'status' => BorrowingStatus::RETURNED,
                'returned_at' => $returnDate,
                'processed_by' => $processor->id,
                'return_condition' => $condition,
                'return_notes' => $notes,
                'fine_amount' => $fineAmount,
            ]);

            // Handle Asset Status & Damage Report
            $assetEvent = 'returned';
            $assetEventDesc = 'Aset dikembalikan dalam kondisi '.$condition->value.'.';

            switch ($condition) {
                case ReturnCondition::GOOD:
                    $asset->update(['status' => AssetStatus::AVAILABLE]);
                    break;

                case ReturnCondition::MINOR_DAMAGE:
                    $asset->update(['status' => AssetStatus::AVAILABLE]);
                    DamageReport::create([
                        'organization_id' => $asset->organization_id,
                        'asset_id' => $asset->id,
                        'borrowing_id' => $borrowing->id,
                        'reported_by' => $processor->id, // Who processed it
                        'severity' => DamageSeverity::MINOR,
                        'description' => 'Kerusakan ringan dicatat saat pengembalian: '.$notes,
                        'status' => DamageReportStatus::NOTED,
                    ]);
                    $assetEvent = 'damage_reported';
                    $assetEventDesc = 'Aset dikembalikan dengan kerusakan ringan.';
                    break;

                case ReturnCondition::MAJOR_DAMAGE:
                    $asset->update(['status' => AssetStatus::MAINTENANCE]);
                    DamageReport::create([
                        'organization_id' => $asset->organization_id,
                        'asset_id' => $asset->id,
                        'borrowing_id' => $borrowing->id,
                        'reported_by' => $processor->id,
                        'severity' => DamageSeverity::MAJOR,
                        'description' => 'Kerusakan berat dicatat saat pengembalian: '.$notes,
                        'status' => DamageReportStatus::IN_MAINTENANCE,
                    ]);
                    $assetEvent = 'maintenance_started';
                    $assetEventDesc = 'Aset dikembalikan dengan kerusakan berat dan masuk masa perbaikan.';
                    break;

                case ReturnCondition::LOST:
                    $asset->update(['status' => AssetStatus::LOST]);
                    $assetEvent = 'marked_lost';
                    $assetEventDesc = 'Aset dilaporkan hilang saat pengembalian.';
                    break;
            }

            // Log Asset
            $asset->logs()->create([
                'organization_id' => $asset->organization_id,
                'user_id' => $processor->id,
                'event' => $assetEvent,
                'description' => $assetEventDesc,
                'metadata' => ['borrowing_id' => $borrowing->id],
            ]);

            event(new BorrowingReturned($borrowing));

            return $borrowing;
        });
    }
}
