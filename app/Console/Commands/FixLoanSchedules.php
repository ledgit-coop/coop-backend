<?php

namespace App\Console\Commands;

use App\Models\LoanSchedule;
use Illuminate\Console\Command;

class FixLoanSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan-schedules:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix loan schedules';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Reset the paid flag if the amount paid is less than 0
        LoanSchedule::where('paid', true)->where('amount_paid', '<=', '0')->update([
            'paid' => true,
            'overdue' => false,
        ]);
      
        return Command::SUCCESS;
    }
}
