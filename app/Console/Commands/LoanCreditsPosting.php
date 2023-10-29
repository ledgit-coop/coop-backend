<?php

namespace App\Console\Commands;

use App\Helpers\MemberAccounHelper;
use App\Models\Loan;
use Illuminate\Console\Command;

class LoanCreditsPosting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:fee-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post loan fee credits';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $loans = Loan::where('released', true)->with(['loan_fees' => function ($query) {
            $query->whereHas('loan_fee_template', function($template) {
                $template->where('credit_share_capital', false)->where('credit_regular_savings', false);
            });
        }])->get();

        foreach ($loans as $loan) {
            MemberAccounHelper::recordFeeCredits($loan);
        }
    }
}
