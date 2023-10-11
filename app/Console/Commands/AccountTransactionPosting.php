<?php

namespace App\Console\Commands;

use App\Models\AccountTransaction;
use Illuminate\Console\Command;

class AccountTransactionPosting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account-transaction:posting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post account transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find the ID of the last record
        $lastRecordId = AccountTransaction::orderBy('transaction_date', 'desc')->first()->id;

        // Update the 'posted' column to true for all records except the last one
        AccountTransaction::where('id', '!=', $lastRecordId)->update(['posted' => true]);
        
        return Command::SUCCESS;
    }
}
