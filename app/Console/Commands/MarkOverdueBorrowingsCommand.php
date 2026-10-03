<?php

namespace App\Console\Commands;

use App\Actions\Borrowing\MarkOverdueBorrowings;
use Illuminate\Console\Command;

class MarkOverdueBorrowingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrowings:mark-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all borrowed items that have passed their due date as overdue.';

    /**
     * Execute the console command.
     */
    public function handle(MarkOverdueBorrowings $action)
    {
        $this->info('Checking for overdue borrowings...');
        $count = $action->execute();
        $this->info("Successfully marked {$count} borrowings as overdue.");
    }
}
