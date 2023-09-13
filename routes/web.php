<?php

use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestPeriod;
use App\Helpers\LoanHelper;
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

function calculateLoanInterest($loanAmount, $interestRate, $interestFrequency, $loanDuration, $durationUnit) {
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

    // Calculate the interest amount
    $interestAmount = $loanAmount * ($monthlyInterestRate / 100) * $loanDurationInMonths;

    return $interestAmount;
}


function calculateLoanSchedule($loanAmount, $interestRate, $loanTenureMonths, $method, $repaymentCount, $repaymentCycle) {
    $monthlyInterestRate = ($interestRate) / 100;
    $monthlyPayment = 0;
    $loanSchedule = [];

    if ($method === "fixed") {
        // Fixed Interest Method
        $totalInterestPayable = ($loanAmount * $monthlyInterestRate * $loanTenureMonths);
        $totalPayment = $loanAmount + $totalInterestPayable;

        $monthlyPayment = $totalPayment / $loanTenureMonths;
        $principalPayment = round($loanAmount / $repaymentCount, 2);
        $total_principal = $principalPayment * $repaymentCount;
        $remaning_loan = $loanAmount;
        
        for ($i = 1; $i <= $repaymentCount; $i++) {
            // Adjust on the maturity
            if($i == $repaymentCount) {
                $principalPayment = $principalPayment - ($total_principal - $loanAmount);
            }

            $interestPayment = round(($totalInterestPayable / $repaymentCount), 2);
            $totalPayment = $principalPayment + $interestPayment;
            $remaning_loan = $remaning_loan - $principalPayment;

            $loanSchedule[] = [
                'Month' => $i,
                'Principal' => $principalPayment,
                'Interest' => $interestPayment,
                'TotalPayment' => $totalPayment,
                'LoanBalance' => $remaning_loan
            ];
        } 
    } elseif ($method === "reducing balance") {
        // Reducing Balance Method
        $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$loanTenureMonths));

        for ($i = 1; $i <= $loanTenureMonths; $i++) {
            $interestPayment = ($loanAmount * $monthlyInterestRate);
            $principalPayment = $monthlyPayment - $interestPayment;
            $loanAmount -= $principalPayment;

            $loanSchedule[] = [
                'Month' => $i,
                'Principal' => $principalPayment,
                'Interest' => $interestPayment,
                'TotalPayment' => $monthlyPayment,
                'LoanBalance' => $loanAmount
            ];
        }
    } else {
        return "Invalid method. Please choose 'fixed' or 'reducing balance'.";
    }

    return $loanSchedule;
}

function printLoanScheduleTable($loanSchedule) {
    echo "<table border='1'>";
    echo "<tr><th>Month</th><th>Principal</th><th>Interest</th><th>Total Payment</th><th>Loan Balance</th></tr>";

    $totalPrincipal = 0;
    $totalInterest = 0;
    $totalPayment = 0;

    foreach ($loanSchedule as $payment) {
        echo "<tr>";
        echo "<td>{$payment['Month']}</td>";
        echo "<td>Rs. {$payment['Principal']}</td>";
        echo "<td>Rs. {$payment['Interest']}</td>";
        echo "<td>Rs. {$payment['TotalPayment']}</td>";
        echo "<td>Rs. {$payment['LoanBalance']}</td>";
        echo "</tr>";

        $totalPrincipal += $payment['Principal'];
        $totalInterest += $payment['Interest'];
        $totalPayment += $payment['TotalPayment'];
    }

    echo "<tr>";
    echo "<td colspan='1'><strong>Total:</strong></td>";
    echo "<td><strong>Rs. {$totalPrincipal}</strong></td>";
    echo "<td><strong>Rs. {$totalInterest}</strong></td>";
    echo "<td><strong>Rs. {$totalPayment}</strong></td>";
    echo "<td></td>";
    echo "</tr>";

    echo "</table>";
}
 
Route::get('/', function () {

    $loanAmount = 50000; // Loan amount in dollars
    $interestRate = 10; // Interest rate (per day, per week, per month, or per year)
    $interestFrequency = LoanInterestPeriod::PER_DAY; // Interest frequency ('daily', 'weekly', 'monthly', 'yearly')
    $loanDuration = 1; // Loan duration (in days, weeks, months, or years)
    $durationUnit =LoanDurationPeriod::YEARS; // Duration unit ('days', 'weeks', 'months', 'years')
    
    $interestAmount = calculateLoanInterest($loanAmount, $interestRate, $interestFrequency, $loanDuration, $durationUnit);

    
    dd($interestAmount);

 // Example usage:
$loanAmount = 50000; // Loan amount in Rs.
$annualInterestRate = 10; // Annual interest rate (in percentage)
$loanTenureMonths = 1; // Loan tenure in months
$method = "fixed"; // Choose either "fixed" or "reducing balance"

$loanSchedule = calculateLoanSchedule($loanAmount, $annualInterestRate, $loanTenureMonths, $method, 1, 'months');

// Print the loan schedule as a table
printLoanScheduleTable($loanSchedule);

 

});
