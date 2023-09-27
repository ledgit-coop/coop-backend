<?php

namespace App\Helpers\LoanCalculator;

use App\Constants\LoanDurationPeriod;
use App\Constants\LoanFeeType;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanRepaymentCycle;
use App\Helpers\LoanCalculator\LoanFee\LoanFee;
use App\Models\Loan;
use App\Models\LoanFeeTemplate;
use Exception;
use Illuminate\Support\Carbon;

class LoanCalculator {

    protected $schedules;
    protected $total_principal;
    protected $total_interest;
    protected $total_payment;
    protected $fees;
    protected $amortization_starting_date;
    protected $maturity_date;
    protected $released_date;

    // Options
    public bool $round_results = true;

    public function __construct()
    {
        $this->fees = collect([]);
    }

    protected function calculateFlatLoanInterest($loanAmount, $interestRate, $interestFrequency, $loanDuration, $durationUnit) {
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
    
    protected function getPayrollBiweeklyDate(Carbon $next_payroll_date) {

        $daysToAdd = 15;
    
        if ($next_payroll_date->day >= 30 && $next_payroll_date->daysInMonth > 30)
            $daysToAdd++;
        else if($next_payroll_date->daysInMonth >= 28)
            $daysToAdd =( 30 - $next_payroll_date->daysInMonth )+ $daysToAdd;

        return ($next_payroll_date->clone())->addDays($daysToAdd);
    }

    public function getStartDate($repaymentCycle, $released_date, $payroll = false) {

        // Set intial amortization
        $start_date = new Carbon($released_date);
        
        // Compute the cycly by payroll date
        if($payroll) {
            if($repaymentCycle == LoanRepaymentCycle::MONTHLY)
                return $start_date->add('month', 1);
            elseif($repaymentCycle == LoanRepaymentCycle::BIWEEKLY) {
                
                $daysToAdd = 15;
                $end_of_month = $start_date->clone()->endOfMonth();
                $daysDiff = $start_date->diffInDays($end_of_month);
                $daysInMonth = $start_date->daysInMonth;
            
                if($daysDiff > 0 && $daysDiff < 15) {
                    if($daysInMonth > 30) {
                        $daysToAdd += ($start_date->daysInMonth - 30); // Excess of 30 days
                    }
                    else if($daysInMonth < 30)
                    {
                        $daysToAdd -= (30 - $start_date->daysInMonth);
                       
                    }
                }
                return $start_date->add('day', $daysToAdd);

            } else {
                throw new Exception("Payroll repayment cycle not supported.", 1);
                
            }
        }
        else
            return $start_date->add(...LoanRepaymentCycle::CARBON[$repaymentCycle])->setDay($start_date->day);
    }

    protected function calculateLoanSchedule($loanAmount, $interestRate, $loanDuration, $method, $repaymentCount, $repaymentCycle, $durationUnit, $interestFrequency, $released_date, $next_payroll_date = null) {
        
        $loanSchedule = [];
    
        $start_date = new Carbon($next_payroll_date ? $next_payroll_date : $released_date);
        $isPayroll = !(!$next_payroll_date);
        // Set intial amortization
        $start_date = $this->getStartDate($repaymentCycle, $start_date, $isPayroll);
        //$start_date->add(...LoanRepaymentCycle::CARBON[$repaymentCycle])->setDay($start_date->day);

        // Next payroll date will be applied only in biweekly and monthly
        if($next_payroll_date && in_array($repaymentCycle, [LoanRepaymentCycle::BIWEEKLY, LoanRepaymentCycle::MONTHLY])) {
            
            if($repaymentCycle == LoanRepaymentCycle::BIWEEKLY)
                $next_payroll_date = new Carbon($next_payroll_date);
        }
    
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
                $remaning_loan = round($remaning_loan - $principalPayment, 2);

                $loanSchedule[] = (new LoanSchedule(
                    $i, 
                    $start_date,
                    $principalPayment,
                    $interestPayment,
                    $totalPayment,
                    $remaning_loan,
                    $description,
                ))->toArray();

                //$start_date->add(...LoanRepaymentCycle::CARBON[$repaymentCycle])->setDay($start_date->day);
                $start_date = $this->getStartDate($repaymentCycle, $start_date, $isPayroll);
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
    
    public function generateSchedule(
        $loanAmount,
        $interestRate,
        $loanDuration,
        $method,
        $repaymentCount,
        $repaymentCycle,
        $durationUnit,
        $interestFrequency,
        $released_date,
        $next_payroll_date = null) {

        $loanSchedule = $this->calculateLoanSchedule(
            $loanAmount,
            $interestRate,
            $loanDuration,
            $method,
            $repaymentCount,
            $repaymentCycle,
            $durationUnit,
            $interestFrequency,
            $released_date,
            $next_payroll_date
        );

        $totalPrincipal = 0;
        $totalInterest = 0;
        $totalPayment = 0;
    
        foreach ($loanSchedule as $payment) {
            $totalPrincipal += $payment['principal'];
            $totalInterest += $payment['interest'];
            $totalPayment += $payment['total_payment'];
        }

        // Populate properties
        $this->schedules = $loanSchedule;
        $this->total_principal = $totalPrincipal;
        $this->total_interest = $totalInterest;
        $this->total_payment = $totalPayment;
        $this->amortization_starting_date = count($loanSchedule) ? $loanSchedule[0]['date'] : null;
        $this->maturity_date = count($loanSchedule) ? $loanSchedule[count($loanSchedule) -1]['date'] : null;
        $this->released_date = $released_date;

        return $this->toArray();
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
            $loan->next_payroll_date
        );
    }

    public function addFee(LoanFee $fee) {
        $this->fees->add($fee);
    }


    public function addFeeOnTemplate(LoanFeeTemplate $template, float $fee, float $amount) {
        $this->addFee(new LoanFee(
            $template->id,
            $template->name,
            $fee,
            $template->fee_type,
            $template->fee_method,
            $amount,
        ));
    }


    public function toArray() {

        // Compute principal dedictible fees
        $fees = [];

        foreach ($this->fees->where('type', LoanFeeType::DEDUCT_PRINCIPAL) as $fee) {
            $fees[] = (object) [
                'id' => $fee->id,
                'name' => $fee->name,
                'amount' => round($fee->fee(), 2)
            ];
        }

        $fees = collect($fees);

        return [
            'schedules' => $this->schedules,
            'total_principal' => $this->total_principal,
            'total_interest' => $this->total_interest,
            'total_payment' => round($this->total_payment),
            'fees' => $fees,
            'amortization_starting_date' => $this->amortization_starting_date,
            'maturity_date' => $this->maturity_date,
            'released_date' => $this->released_date,
            'released_amount' => round($this->total_principal - ($fees->sum('amount')))
        ];
    }
}

