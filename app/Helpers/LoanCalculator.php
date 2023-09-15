<?php

namespace App\Helpers;

use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanRepaymentCycle;
use App\Models\Loan;
use Illuminate\Support\Carbon;

class LoanCalculator {

    public function calculateFlatLoanInterest($loanAmount, $interestRate, $interestFrequency, $loanDuration, $durationUnit) {
        // Convert the interest rate to a monthly rate
        if ($interestFrequency === LoanInterestPeriod::PER_DAY) {
            $monthlyInterestRate = $interestRate * 30; // Assuming an average of 30 days in a month
        } elseif ($interestFrequency === LoanInterestPeriod::PER_WEEK) {
            $monthlyInterestRate = $interestRate * 4; // Assuming 4 weeks in a month
        } elseif ($interestFrequency === LoanInterestPeriod::PER_MONTH) {
            $monthlyInterestRate = $interestRate;
        } elseif ($interestFrequency === LoanInterestPeriod::PER_YEAR) {
            $monthlyInterestRate = $interestRate / 12; // Assuming 12 months in a year
        } else {
            return "Invalid interest frequency. Please choose 'daily', 'weekly', 'monthly', or 'yearly'.";
        }
    
        // Convert the loan duration to months
        if ($durationUnit === LoanDurationPeriod::DAYS) {
            $loanDurationInMonths = $loanDuration / 30; // Assuming an average of 30 days in a month
        } elseif ($durationUnit === LoanDurationPeriod::WEEKS) {
            $loanDurationInMonths = $loanDuration / 4; // Assuming 4 weeks in a month
        } elseif ($durationUnit === LoanDurationPeriod::MONTHS) {
            $loanDurationInMonths = $loanDuration;
        } elseif ($durationUnit === LoanDurationPeriod::YEARS) {
            $loanDurationInMonths = $loanDuration * 12; // Assuming 12 months in a year
        } else {
            return "Invalid duration unit. Please choose 'days', 'weeks', 'months', or 'years'.";
        }
    
        $interest = ($monthlyInterestRate / 100);
        // Calculate the interest amount
        $interestAmount = $loanAmount * $interest * $loanDurationInMonths;
    
        return [
            $interestAmount,
            $interest,
        ];
    }
    
    public function calculateLoanSchedule($loanAmount, $interestRate, $loanDuration, $method, $repaymentCount, $repaymentCycle, $durationUnit, $interestFrequency, $amortization_start_date) {
        
        $loanSchedule = [];
    
        // Set intial amortization
        $start_date = new Carbon($amortization_start_date);
        $start_date->add(...LoanRepaymentCycle::CARBON[$repaymentCycle])->setDay($start_date->day);
    
        if ($method === LoanInterestMethod::FLAT_RATE) {
            // Fixed Interest Method
            [$totalInterestPayable] = $this->calculateFlatLoanInterest($loanAmount, $interestRate, $interestFrequency, $loanDuration, $durationUnit);
    
            $totalPayment = $loanAmount + $totalInterestPayable;
            $principalPayment = round($loanAmount / $repaymentCount, 2);
            $total_principal = $principalPayment * $repaymentCount;
    
            // Set initial loan
            $remaning_loan = $loanAmount;
            
            for ($i = 1; $i <= $repaymentCount; $i++) {
                
                $description = 'amortization';

                // Adjust on the maturity
                if($i == $repaymentCount) {
                    $description = 'maturity';
                    $principalPayment = $principalPayment - ($total_principal - $loanAmount);
                }
    
                $interestPayment = ($totalInterestPayable / $repaymentCount);
                $totalPayment = $principalPayment + $interestPayment;
                $remaning_loan = ($remaning_loan - $principalPayment);

                $loanSchedule[] = [
                    'month' => $i, 
                    'date' => $start_date->format("Y-m-d"),
                    'principal' => $principalPayment,
                    'interest' => $interestPayment,
                    'total_payment' => $totalPayment,
                    'loan_balance' => $remaning_loan,
                    'description' => $description,
                ];
    
                $start_date->add(...LoanRepaymentCycle::CARBON[$repaymentCycle])->setDay($start_date->day);
            } 
        } 
        // elseif ($method === LoanInterestMethod::REDUCING_BALANCE_EQUAL_INSTALLMENTS) {
        //     // Reducing Balance Method
        //     $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$loanTenureMonths));
    
        //     for ($i = 1; $i <= $loanTenureMonths; $i++) {
        //         $interestPayment = ($loanAmount * $monthlyInterestRate);
        //         $principalPayment = $monthlyPayment - $interestPayment;
        //         $loanAmount -= $principalPayment;
    
        //         $loanSchedule[] = [
        //             'Month' => $i,
        //             'Principal' => $principalPayment,
        //             'Interest' => $interestPayment,
        //             'TotalPayment' => $monthlyPayment,
        //             'LoanBalance' => $loanAmount
        //         ];
        //     }
        // } 
        else {
            return "Invalid method. Please choose 'fixed' or 'reducing balance'.";
        }
    
        return $loanSchedule;
    }
    
    public function generateSchedule($loanAmount, $interestRate, $loanDuration, $method, $repaymentCount, $repaymentCycle, $durationUnit, $interestFrequency, $amortization_start_date) {

        $loanSchedule = $this->calculateLoanSchedule($loanAmount, $interestRate, $loanDuration, $method, $repaymentCount, $repaymentCycle, $durationUnit, $interestFrequency, $amortization_start_date);

        $totalPrincipal = 0;
        $totalInterest = 0;
        $totalPayment = 0;
    
        foreach ($loanSchedule as $payment) {
            $totalPrincipal += $payment['principal'];
            $totalInterest += $payment['interest'];
            $totalPayment += $payment['total_payment'];
        }
        

        return [
            'schedules' => $loanSchedule,
            'total_principal' => $totalPrincipal,
            'total_interest' => $totalInterest,
            'total_payment' => $totalPayment,
        ];
    }

    public function makeLoanSchedule(Loan $loan) {
        return $this->generateSchedule(
            $loan->principal_amount, 
            $loan->loan_interest, 
            $loan->loan_duration, 
            $loan->interest_method, 
            $loan->number_of_repayments, 
            $loan->repayment_cycle, 
            $loan->loan_duration_type, 
            $loan->loan_interest_period, 
            $loan->released_date,
        );
    }
}

