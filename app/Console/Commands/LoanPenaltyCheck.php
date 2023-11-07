<?php

namespace App\Console\Commands;

use App\Constants\MemberLoanStatus;
use App\Helpers\LoanHelper;
use App\Models\LoanSchedule;
use Illuminate\Console\Command;

class LoanPenaltyCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'penalty:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Penalty checker';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $loan_schedules = LoanSchedule::select(['loan_schedules.*'])
            ->where('loan_schedules.overdue', true)
            ->join('loans', 'loan_schedules.loan_id', '=', 'loans.id')
            ->where('loan_schedules.paid', false)
            ->with('loan')
            ->whereNotNull('loans.penalty')
            ->where('loans.released', true)
            ->where('loans.status', '<>', MemberLoanStatus::CLOSED)
            ->get();
  
        foreach ($loan_schedules as $loan_schedule) {
            LoanHelper::applyAmortizationPenalty($loan_schedule);
        }

        return Command::SUCCESS;
    }
}
