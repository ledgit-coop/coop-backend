<?php

namespace App\Helpers;

use App\Models\Loan;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class LoanHelper {

    public static function calculateLoanSchedule($loanAmount, $annualInterestRate, $loanTermMonths, $loanStartDate, $interestMethod, $interestRateFrequency, $repaymentSchedule) {
        // Convert annual interest rate to the specified rate frequency
        // if ($interestRateFrequency === "daily") {
        //     $monthlyInterestRate = ($annualInterestRate / 100) / 365 / 30; // Assuming 30 days in a month
        // } elseif ($interestRateFrequency === "weekly") {
        //     $monthlyInterestRate = ($annualInterestRate / 100) / 52; // Assuming 52 weeks in a year
        // } elseif ($interestRateFrequency === "monthly") {
        //     $monthlyInterestRate = ($annualInterestRate / 100) / 12;
        // } elseif ($interestRateFrequency === "yearly") {
        //     $monthlyInterestRate = ($annualInterestRate / 100) / 12;
        // } else {
        //     throw new InvalidArgumentException("Invalid interest rate frequency");
        // }

 
        $monthlyInterestRate = $annualInterestRate;

        // Calculate the monthly payment using the formula for fixed-rate loans
        $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$loanTermMonths));
        dd($monthlyPayment);
        // Initialize variables to track the schedule
        $schedule = [];
        $remainingBalance = $loanAmount;
        
        // Define an array of cycle multipliers for different repayment schedules
        $cycleMultipliers = [
            'Daily' => 30.44, // Assuming 30.44 days in a month on average
            'Weekly' => 4.33, // Assuming 4.33 weeks in a month on average
            'Biweekly' => 2.17, // Half of weekly
            'Monthly' => 1,
            'Bimonthly' => 0.5, // Every two months
            'Quarterly' => 0.25, // Every three months
            'Every 4 Months' => 0.1667, // Every four months
            'Semi-Annual' => 0.0833, // Every six months
            'Every 9 Months' => 0.0556, // Every nine months
            'Yearly' => 0.0833, // Every twelve months
            'Lump-Sum' => 0, // No regular payments
        ];
        
        // Calculate the cycle multiplier based on the selected repayment schedule
        if (!isset($cycleMultipliers[$repaymentSchedule])) {
            throw new InvalidArgumentException("Invalid repayment schedule");
        }
        
        $cycleMultiplier = $cycleMultipliers[$repaymentSchedule];
        
        // Calculate the amortization schedule
        for ($month = 1; $month <= $loanTermMonths; $month++) {
            if ($interestMethod === "flat_rate") {
                $interestPayment = ($loanAmount * $monthlyInterestRate * $cycleMultiplier);
            } elseif ($interestMethod === "reducing_balance_equal_to_installment") {
                $interestPayment = $remainingBalance * $monthlyInterestRate * $cycleMultiplier;
            } elseif ($interestMethod === "reducing_balance_equal_to_principal") {
                $interestPayment = $remainingBalance * $monthlyInterestRate * $cycleMultiplier;
                $principalPayment = $monthlyPayment - $interestPayment;
            }
            
            if ($interestMethod !== "reducing_balance_equal_to_principal") {
                $principalPayment = $monthlyPayment - $interestPayment;
            }

            $remainingBalance -= $principalPayment;
            
            // Calculate the payment date for this month based on the selected repayment schedule
            $paymentDate = date('Y-m-d', strtotime("+$cycleMultiplier months", strtotime($loanStartDate)));
            
            // Store the details for this month in the schedule
            $schedule[] = [
                'Month' => $month,
                'PaymentDate' => $paymentDate,
                'Payment' => $monthlyPayment * $cycleMultiplier, // Adjusted for the cycle multiplier
                'Principal' => $principalPayment,
                'Interest' => $interestPayment,
                'Balance' => $remainingBalance,
            ];
            
            // Update the loan start date for the next cycle
            $loanStartDate = $paymentDate;
        }
        
        // Calculate the total interest paid
        $totalInterestPaid = $loanAmount - $remainingBalance;
        
        return [
            'MonthlyPayment' => $monthlyPayment * $cycleMultiplier, // Adjusted for the cycle multiplier
            'TotalInterestPaid' => $totalInterestPaid,
            'AmortizationSchedule' => $schedule,
        ];
    }



    public static function calculateLoanSchedule2($principalAmount, $loanReleaseDate, $interestRatePercentage,
                                $interestMethod, $loanInterestPercentage, $loanInterestFrequency,
                                $loanDuration, $repaymentCycle) {

        // Convert interest rate percentage to decimal
        $interestRate = $interestRatePercentage / 100.0;

        // Calculate the number of payment periods based on loan duration and repayment cycle
        switch ($loanDuration[0]) {
            case 'D':
                $totalPeriods = (int) substr($loanDuration, 1);
                break;
            case 'W':
                $totalPeriods = (int) substr($loanDuration, 1) * 7;
                break;
            case 'M':
                $totalPeriods = (int) substr($loanDuration, 1) * 30;
                break;
            case 'Y':
                $totalPeriods = (int) substr($loanDuration, 1) * 365;
                break;
            default:
                throw new Exception("Invalid loan duration format");
        }

        // Initialize variables
        $schedule = [];
        $currentDate = $loanReleaseDate;
        $remainingPrincipal = $principalAmount;
        $interestFrequency = ["Day" => 365, "Week" => 52, "Month" => 12, "Year" => 1, "Loan" => $totalPeriods];
        $interestPeriod = $interestFrequency[$loanInterestFrequency];

        for ($period = 0; $period < $totalPeriods; $period++) {
            if ($interestMethod == "Flat Rate") {
                $interestPayment = $remainingPrincipal * $interestRate;
            } elseif ($interestMethod == "Reducing Balance equal installments" || $interestMethod == "Reducing balance equal principal") {
                $interestPayment = $remainingPrincipal * $interestRate / $interestPeriod;
            } else {
                throw new Exception("Invalid interest method");
            }

            if ($repaymentCycle == "Lump-Sum") {
                $principalPayment = $remainingPrincipal;
            } else {
                $principalPayment = ($principalAmount / $totalPeriods) - $interestPayment;
            }

            $totalPayment = $principalPayment + $interestPayment;
            $remainingPrincipal -= $principalPayment;

            $schedule[] = [
                "Date" => $currentDate->format('Y-m-d'),
                "Principal Payment" => round($principalPayment, 2),
                "Interest Payment" => round($interestPayment, 2),
                "Total Payment" => round($totalPayment, 2),
                "Remaining Principal" => round($remainingPrincipal, 2)
            ];

            $currentDate->add(new DateInterval('P' . $interestPeriod . 'D'));
        }

        return $schedule;
    }

    public static function makeSchedule(Loan $loan) {

        $loanReleaseDate = new Carbon($loan->releasing_date ?? null);

        $schedules = [];
        for ($i=0; $i < $loan->number_of_repayments; $i++) {
            $schedules[] = [
                'due_date' => $loanReleaseDate->addDay(),
                'principal_amount' => 0,
                'interest_amount' => 0,
                'fee_amount' => 0,
                'penalty_amount' => 0,
                'due_amount' => 0,
                'principal_balance' => $loan->applied_amount,
            ];
        }
        
        $loan->loan_schedules()->createMany($schedules);

    }

}