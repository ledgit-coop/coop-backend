<?php

namespace App\Helpers;

use App\Helpers\LoanCalculator\LoanCalculator;
use App\Models\Loan;
use App\Models\LoanSchedule;
use Exception;

class LoanHelper {

    public static function makeSchedule(Loan $loan) {

        $calculator = new LoanCalculator();

        $fees = $loan->loan_fees;
        foreach ($fees as $fee) {
            $calculator->addFeeOnTemplate(
                $fee->loan_fee_template,
                $fee->fee,
                $loan->principal_amount,
            );
        }

        $computation = $calculator->makeLoanSchedule($loan);

        if($computation['fees']) {
            foreach ($computation['fees'] as $fee) {
                $loan->loan_fees()->where('loan_fee_template_id', $fee->id)->update([ 'amount' => $fee->amount ]);
            }
        }

        $loan->released_amount = $computation['released_amount'];
        $loan->interest_amount = $computation['total_interest'];
        $loan->due_amount = $computation['total_payment'];
        $loan->save();

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

    public static function reComputeSchedule(Loan $loan) {

        // @Notes: Need to refactor, this should not be deleted just updating
        $loan->loan_schedules()->delete();
        self::makeSchedule($loan);

    }

    public static function generateUniqueTransactionNumber() {
   
        $loan = Loan::orderBy('loan_number', 'desc')->first();

        if($loan)
            return ((int) $loan->loan_number) + 1;

        return "10000000001";
    }

    public static function updatePayment(LoanSchedule $schedule, float $paymentAmount)
    {
        if(!$schedule->paid) {
            // Check if there is an outstanding balance on this schedule.
            $outstandingBalance = $schedule->due_amount - $schedule->amount_paid;

            // Round the payment amount to two decimal places.
            $paymentAmount = round($paymentAmount, 2);


            if ($paymentAmount >= $outstandingBalance) {
                // Payment amount exceeds or equals the outstanding balance.
                $schedule->amount_paid += $outstandingBalance;
                $schedule->paid = true;
                $schedule->save();

                // Calculate the excess payment.
                $excessPayment = $paymentAmount - $outstandingBalance;

                // Check if there is a next amortization schedule.
                $nextSchedule = self::getNextSchedule($schedule);

                if ($nextSchedule && $excessPayment > 0) {
                    // Offset the excess payment to the next amortization.
                    self::updatePayment($nextSchedule, $excessPayment);
                }
            } else {
                // Payment amount is less than the outstanding balance.
                $schedule->amount_paid += $paymentAmount;
                $schedule->save();
            }

            return $schedule;
        }

        throw new Exception("Loan amortization already paid.", 1);
    }

    public static function getNextSchedule(LoanSchedule $schedule)
    {
        return LoanSchedule::where('loan_id', $schedule->loan_id)
            ->where('due_date', '>', $schedule->due_date)
            ->orderBy('due_date', 'asc')
            ->first();
    }
}