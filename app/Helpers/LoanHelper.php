<?php

namespace App\Helpers;

use App\Constants\LoanPenaltyFrequency;
use App\Constants\LoanPenaltyMethod;
use App\Helpers\LoanCalculator\LoanCalculator;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Log;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        $currentYear = Carbon::now()->format('ymd');

        $count = Loan::count();

        $sequence = sprintf('%012d', $count + 1);

        return  $currentYear . $sequence;
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

                if($excessPayment > 0)
                    if ($nextSchedule) {
                        // Offset the excess payment to the next amortization.
                        return self::updatePayment($nextSchedule, $excessPayment);
                    } else {
                        // No next schedule payment put all excess to the last payment
                        $schedule->amount_paid += $excessPayment;
                        $schedule->save();
                    }

            } else {
                // Payment amount is less than the outstanding balance.
                $schedule->amount_paid += $paymentAmount;
                $schedule->save();
            }

            // Log Payment
            LogHelper::logLoanPayment($schedule);

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

    public static function calculateLoanPenalty(
            $loanAmount,
            $penaltyAmountOrRate,
            $penaltyType) {

        if(!in_array($penaltyType, LoanPenaltyMethod::LIST))
            throw new Exception("Penalty method not supported.", 1);

        return round($penaltyType === LoanPenaltyMethod::PERCENTAGE ? $loanAmount * ($penaltyAmountOrRate / 100) : $penaltyAmountOrRate, 2);
    }

    public static function computeLoanPreTerminationFee(Loan $loan) : Loan {
        // Do not update if loan released
        if(!$loan->released) {
            $loan->pre_termination_fee = LoanHelper::calculateLoanPenalty(
                $loan->principal_amount,
                $loan->pre_termination_panalty,
                $loan->pre_termination_panalty_method,
            );
    
            $loan->saveQuietly();
        }

        return $loan;
    }

    public static function applyAmortizationPenalty(LoanSchedule $schedule) {
        // Past due grace period and not paid
        if(abs($schedule->due_days) > $schedule->penalty_grace_period && $schedule->paid == false) {
            $now = Carbon::now();
            $latest_penalty = $schedule->latest_penalty();
            $extended_penalty = false;

            if($latest_penalty) {
                $last_penalty_date = $latest_penalty->penalty_date;
                $days_gap = 0;
                $difference = Helper::diffDays($last_penalty_date, Carbon::now());

                switch ($schedule->loan->penalty_duration) {
                    case LoanPenaltyFrequency::EVERY_DAY:
                        $days_gap = 1;
                        break;
                    case LoanPenaltyFrequency::EVERY_WEEK:
                        $days_gap = 7;
                        break;
                    case LoanPenaltyFrequency::EVERY_MONTH:
                        $days_gap = 31;
                        break;
                    case LoanPenaltyFrequency::EVERY_YEAR:
                        $days_gap = 365;
                        break;      
                }

                // Due from the last penalty
                $extended_penalty = $difference >= $days_gap;
            }

            if(!$latest_penalty || $extended_penalty) {

               try {
                    DB::beginTransaction();
                    $penalty = self::calculateLoanPenalty($schedule->due_amount, $schedule->loan->penalty, $schedule->loan->penalty_method);
                    $schedule->penalty_amount += $penalty; 
                    $schedule->due_amount += $penalty;
                    $schedule->save();

                    $schedule->loan_schedule_penalties()->create([
                        'penalty' => $schedule->penalty_amount,
                        'penalty_date' => $now->format('Y-m-d'),
                        'frequency' => $schedule->loan->penalty_duration,
                        'method' => $schedule->loan->penalty_method,
                    ]);

                    DB::commit();
               } catch (\Throwable $th) {
                    DB::rollBack();
                    throw $th;
               }
            }
        }
    }
}