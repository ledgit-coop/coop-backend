<?php

namespace App\Helpers;

use App\Models\Loan;
use Illuminate\Support\Carbon;
class LoanHelper {

    public static function makeSchedule(Loan $loan) {

        $calculator = new LoanCalculator();
        $computation = $calculator->makeLoanSchedule($loan);

        $schedules = collect($computation['schedules'])->map(function($c) {
            return [
                'due_date' => $c['date'],
                'principal_amount' => $c['principal'],
                'interest_amount' => $c['interest'],
                'fee_amount' => 0,
                'penalty_amount' => 0,
                'due_amount' => $c['total_payment'],
                'principal_balance' => $c['loan_balance'],
                'is_maturity' => $c['description'] == 'maturity',
            ];
        });
 
        $loan->loan_schedules()->createMany($schedules);
    }

    public static function generateUniqueTransactionNumber() {
   
        $loan = Loan::orderBy('loan_number', 'desc')->first();

        if($loan)
            return ((int) $loan->loan_number) + 1;

        return "10000000001";
    }
}