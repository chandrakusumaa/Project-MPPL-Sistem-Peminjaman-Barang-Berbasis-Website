<?php

namespace App\Actions\Borrowing;

use App\Enums\AssetStatus;
use App\Enums\BorrowingStatus;
use App\Events\BorrowingRequested;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class RequestBorrowing
{
    public function execute(Organization $organization, Asset $asset, User $user, array $data): Borrowing
    {
        if ($organization->archived_at) {
            throw new Exception('Organisasi diarsipkan, tidak dapat melakukan peminjaman.');
        }

        if (! $user->isMemberOf($organization)) {
            throw new Exception('Hanya anggota organisasi yang dapat melakukan peminjaman.');
        }

        if ($asset->status !== AssetStatus::AVAILABLE) {
            throw new Exception('Aset sedang tidak tersedia.');
        }

        $borrowDate = Carbon::parse($data['borrow_date'])->startOfDay();
        $dueDate = Carbon::parse($data['due_date'])->endOfDay();

        if ($borrowDate->isBefore(Carbon::today())) {
            throw new Exception('Tanggal peminjaman tidak boleh di masa lalu.');
        }

        if ($dueDate->isBefore($borrowDate) || $dueDate->isSameDay($borrowDate)) {
            throw new Exception('Tanggal pengembalian harus setelah tanggal peminjaman.');
        }

        $duration = $borrowDate->diffInDays($dueDate) + 1; // Termasuk hari pinjam
        if ($duration > $organization->max_borrow_days) {
            throw new Exception("Durasi maksimal peminjaman adalah {$organization->max_borrow_days} hari.");
        }

        if (strlen($data['reason']) < 10) {
            throw new Exception('Alasan peminjaman minimal 10 karakter.');
        }

        // Cek duplicate pending request
        $hasPending = Borrowing::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->where('status', BorrowingStatus::PENDING)
            ->exists();

        if ($hasPending) {
            throw new Exception('Anda sudah memiliki pengajuan peminjaman yang sedang menunggu persetujuan untuk aset ini.');
        }

        $borrowing = Borrowing::create([
            'organization_id' => $organization->id,
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'reason' => $data['reason'],
            'status' => BorrowingStatus::PENDING,
        ]);

        event(new BorrowingRequested($borrowing));

        return $borrowing;
    }
}
