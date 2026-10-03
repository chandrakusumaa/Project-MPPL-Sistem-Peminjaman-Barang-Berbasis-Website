<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowingStatus;
use App\Events\BorrowingCancelled;
use App\Models\Borrowing;
use App\Models\User;
use Exception;

class CancelBorrowing
{
    public function execute(Borrowing $borrowing, User $user): Borrowing
    {
        if ($borrowing->user_id !== $user->id) {
            throw new Exception('Anda tidak berhak membatalkan peminjaman ini.');
        }

        if ($borrowing->status !== BorrowingStatus::PENDING) {
            throw new Exception('Hanya peminjaman berstatus pending yang dapat dibatalkan.');
        }

        $borrowing->update([
            'status' => BorrowingStatus::CANCELLED,
        ]);

        event(new BorrowingCancelled($borrowing));

        return $borrowing;
    }
}
