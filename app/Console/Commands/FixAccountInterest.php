<?php

namespace App\Console\Commands;

use App\Helpers\MemberAccounHelper;
use App\Models\AccountTransaction;
use App\Models\MemberAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ComputeAccountEarnInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:fix-interest {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute earn interest per day';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date_arg = $this->argument('date');
        
        $now = $date_arg ? new Carbon($date_arg) : Carbon::now();

        $dates = $now->range(Carbon::now(), '1 day');

        $accounts = MemberAccount::get();

        // Delete all records
        AccountTransaction::where('particular', 'like', '%Earned interest%')->delete();

        foreach ($accounts as $account) {
            MemberAccounHelper::fixAccounBalance($account);
        }
        

        foreach ($dates as $date) {
            MemberAccounHelper::computeSavingsEarnInterest($date);
        }
        
        return Command::SUCCESS;
    }
}
