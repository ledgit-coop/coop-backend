<?php

use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanRepaymentCycle;
use App\Helpers\LoanCalculator;
use App\Helpers\LoanHelper;
use App\Models\Loan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {

    $calculator = new LoanCalculator;
    
    // $loanAmount = 50000; // Loan amount in dollars

    // $interestRate = 10; // Interest rate (per day, per week, per month, or per year)
    // $interestFrequency = LoanInterestPeriod::PER_WEEK; // Interest frequency ('daily', 'weekly', 'monthly', 'yearly')

    // $loanDuration = 5; // Loan duration (in days, weeks, months, or years)
    // $durationUnit =LoanDurationPeriod::WEEKS; // Duration unit ('days', 'weeks', 'months', 'years')

    // $repaymentCount = 10;
    // $repaymentCycle = LoanRepaymentCycle::DAILY;

    // $method = LoanInterestMethod::FLAT_RATE; // Choose either "fixed" or "reducing balance"

    // // $interestAmount = calculateFlatLoanInterest($loanAmount, $interestRate, $interestFrequency, $loanDuration, $durationUnit);

    // $amortization_start_date = '2023-09-18';

 
    // $loanSchedule = $calculator->calculateLoanSchedule($loanAmount, $interestRate, $loanDuration, $method, $repaymentCount, $repaymentCycle, $durationUnit, $interestFrequency, $amortization_start_date);

    // dd($loanSchedule);
    // // Print the loan schedule as a table
    // printLoanScheduleTable($loanSchedule);



    $loan = Loan::first();
    dd($calculator->makeLoanSchedule($loan));



});
