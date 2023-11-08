<?php

namespace App\Console\Commands\Temporal;

use App\Constants\TransactionSubTypes;
use App\Helpers\TransactionHelper;
use App\Models\LoanSchedule;
use App\Models\Transaction;
use App\Models\TransactionSubType;
use App\Models\User;
use Illuminate\Console\Command;

class FixLoanTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:fix-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix loan transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('type', 'system')->first();

        $types = TransactionSubType::whereIn('key', [TransactionSubTypes::LOAN_PRINCIPAL_PAYMENT,TransactionSubTypes::LOAN_INTEREST_PAYMENT,TransactionSubTypes::LOAN_PENALTIES_PAYMENT])->get()->pluck('id');

        // Delete transactions
        Transaction::whereIn('transaction_sub_type_id' , $types->toArray())->forceDelete();

        $amortizations = LoanSchedule::where('paid', true)->get();

        foreach ($amortizations as $schedule) {
            TransactionHelper::makeLoanAmortizationPayment($schedule, $user);
        }
        
        return Command::SUCCESS;
    }
}
