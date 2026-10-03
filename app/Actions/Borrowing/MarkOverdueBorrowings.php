<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowingStatus;
use App\Events\BorrowingBecameOverdue;
use App\Models\Borrowing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarkOverdueBorrowings
{
    public function execute(): int
    {
        $count = 0;

        // Find all borrowed that have past their due date (comparing only the date part via startOfDay)
        // because due_date is usually endOfDay or similar.
        // Or strictly where due_date < now

        Borrowing::where('status', BorrowingStatus::BORROWED)
            ->where('due_date', '<', Carbon::today())
            ->chunkById(100, function ($borrowings) use (&$count) {
                foreach ($borrowings as $borrowing) {
                    $borrowing->update([
                        'status' => BorrowingStatus::OVERDUE,
                        'overdue_at' => now(),
                    ]);

                    $borrowing->asset->logs()->create([
                        'organization_id' => $borrowing->organization_id,
                        'user_id' => null, // Sistem
                        'event' => 'overdue',
                        'description' => 'Peminjaman melewati batas waktu pengembalian.',
                        'metadata' => ['borrowing_id' => $borrowing->id],
                    ]);

                    event(new BorrowingBecameOverdue($borrowing));
                    $count++;
                }
            });

        Log::info("MarkOverdueBorrowings executed: {$count} borrowings marked as overdue.");

        return $count;
    }
}
