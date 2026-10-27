<?php

namespace App\Enums;

enum BorrowingStatus: string
{
    case Pending = 'pending';
    case Borrowed = 'borrowed';
    case Overdue = 'overdue';
    case Returned = 'returned';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
