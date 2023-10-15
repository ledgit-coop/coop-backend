<?php

namespace App\Console\Commands;

use App\Constants\AccountStatus;
use App\Constants\MemberAccountTransactionType;
use App\Helpers\AccountHelper;
use App\Helpers\MemberAccounHelper;
use App\Models\MemberAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        MemberAccounHelper::computeSavingsEarnInterest($now);
        
        return Command::SUCCESS;
    }
}
