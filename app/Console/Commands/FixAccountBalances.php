<?php

namespace App\Console\Commands;

use App\Helpers\MemberAccounHelper;
use App\Models\MemberAccount;
use Illuminate\Console\Command;

class FixAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:fix-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix account balances';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accounts = MemberAccount::get();

        foreach ($accounts as $account) {
            if($account->balance != $account->transactions()->sum('amount'))
                MemberAccounHelper::fixAccounBalance($account);
        }

        return Command::SUCCESS;
    }
}
