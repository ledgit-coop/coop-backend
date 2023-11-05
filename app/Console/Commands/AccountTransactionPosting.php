<?php

namespace App\Console\Commands;

use App\Constants\AccountStatus;
use App\Models\MemberAccount;
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

        $accounts = MemberAccount::where('status', AccountStatus::ACTIVE)->whereHas('transactions', function($transactions) {
            $transactions->where('posted', false);
        })->get();

        foreach ($accounts as $account) {
            $account->transactions()->update(['posted' => true]);
        }
        
        return Command::SUCCESS;
    }
}
