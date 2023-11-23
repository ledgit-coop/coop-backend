<?php

namespace App\Console\Commands;

use App\Constants\MemberLoanStatus;
use App\Models\LoanSchedule;
use Illuminate\Console\Command;

class OverdueLoanAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amortization:overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the loan amortization overdue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $amortizations = LoanSchedule::where('overdue', false)
            ->where('paid', false)
            ->whereHas('loan', function($loan) {
                $loan->where('status', '<>', MemberLoanStatus::CLOSED)
                ->where('released', true);
            })
            ->get();

        foreach ($amortizations as $schedule) {
            $schedule->overdue = $schedule->due_days < 0;
            $schedule->save();
        }

        return Command::SUCCESS;
    }
}
