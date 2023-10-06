<?php

namespace App\Console\Commands;

use App\Constants\MemberLoanStatus;
use App\Helpers\LogHelper;
use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseLoanAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close loan accounts when all paid';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $loans = Loan::where('status','<>', MemberLoanStatus::CLOSED)
            ->where('released', true)
            ->orderBy('id','desc')
            ->get();
 
        try {
            DB::beginTransaction();

            foreach ($loans as $loan) {
                if($loan->paid_count >= $loan->number_of_repayments) {
                    $loan->status = MemberLoanStatus::CLOSED;
                    $loan->save();
                    LogHelper::logLoanStatusChange($loan);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            
            DB::rollBack();
            throw $th;

        }


        return Command::SUCCESS;
    }
}
