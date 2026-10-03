<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowingStatus;
use App\Events\BorrowingRejected;
use App\Models\Borrowing;
use App\Models\User;
use Exception;

class RejectBorrowing
{
    public function execute(Borrowing $borrowing, User $rejector, string $reason): Borrowing
    {
        if ($borrowing->status !== BorrowingStatus::PENDING) {
            throw new Exception('Hanya peminjaman berstatus pending yang dapat ditolak.');
        }

        if (empty(trim($reason))) {
            throw new Exception('Alasan penolakan wajib diisi.');
        }

        $borrowing->update([
            'status' => BorrowingStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        event(new BorrowingRejected($borrowing));

        return $borrowing;
    }
}
