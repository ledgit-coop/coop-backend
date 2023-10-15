<?php

namespace App\Console\Commands;

use App\Constants\AccountStatus;
use App\Constants\MemberAccountTransactionType;
use App\Helpers\AccountHelper;
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
    protected $signature = 'account:interest {date?}';

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

        $accounts = MemberAccount::where('status', AccountStatus::ACTIVE)
            ->where('below_maintaining_balance', false)
            ->whereRaw(DB::raw("balance >= maintaining_balance")) // enforce 2nd layer condition
            ->whereHas('transactions', function($transactions) use($now) {
                $transactions->whereRaw(DB::raw("date(transaction_date) <= '". $now->format('Y-m-d') ."'"));
            })
            ->get();
    
        foreach ($accounts as $account) {
            $interest = AccountHelper::computeEarnInterest($account->balance, $account->earn_interest_per_anum);
            $account->transactions()->createMany([
                [
                    'transaction_number' => AccountHelper::generateTransactionNumber(),
                    'particular' => "Earned interest",
                    'transaction_date' => $now->format('Y-m-d'),
                    'amount' => $interest,
                    'type' => MemberAccountTransactionType::INTEREST_EARNED
                ]
            ]);
        }
        return Command::SUCCESS;
    }
}
