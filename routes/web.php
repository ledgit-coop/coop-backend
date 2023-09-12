<?php

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


// Principal Amount
// Loan Release Date
// Loan Interest Method
//     - Flat Rate
//     - Reducing Balance equal installments
//     - Reducing balance equal principal
// Loan Interest
// Loan Interest Frequency
//     - Per Day
//     - Per Week
//     - Per Month
//     - Per Year
//     - Per Loan
// Loan Duration
//     - Days
//     - Weeks
//     - Months
//     - Years
// Repayment Cycle
//     - Daily
//     - Weekly
//     - Biweekly
//     - Monthly
//     - Bimonthly
//     - Quarterly
//     - Every 4 Months
//     - Semi-Annual 
//     - Every 9 Months 
//     - Yearly 
//     - Lump-Sum

function calculateLoanSchedule(
    $principalAmount, $loanReleaseDate, $loanInterestMethod,
    $loanInterest, $loanInterestFrequency, $loanDurationNumber, $loanDuration, $repaymentCycle
) {

    $period = convertTime($loanDurationNumber, $loanInterestFrequency, $loanDuration);
 
    $loanInterest = $loanInterest / 100;

    if ($loanInterestMethod == "Flat Rate") {
        $interestPayment = $principalAmount * $loanInterest * $period;
    } elseif ($loanInterestMethod == "Reducing Balance equal installments" || $loanInterestMethod == "Reducing balance equal principal") {
        $interestPayment = $principalAmount * $loanInterest / $period;
    } else {
        throw new Exception("Invalid loan interest method");
    }


    dd($interestPayment);
    

}



function convertTime($value, $fromUnit, $toUnit) {
    $units = [
        "Days" => 1,
        "Weeks" => 7,
        "Months" => 30,
        "Years" => 365,
    ];

    if (!array_key_exists($fromUnit, $units) || !array_key_exists($toUnit, $units)) {
        throw new Exception("Invalid time unit");
    }

    $days = $value * $units[$fromUnit];
    $result = $days / $units[$toUnit];

    return $result;
}

Route::get('/', function () {


 
// Example usage
$principalAmount = 10000;
$loanReleaseDate = new DateTime('2023-09-18');
$loanInterestMethod = "Reducing balance equal principal";
$loanInterest = 30; // 5% interest
$loanInterestFrequency = "Months";
$loanDurationNumber = "3";
$loanDuration = "Months";
$repaymentCycle = "Monthly";

$schedule = calculateLoanSchedule($principalAmount, $loanReleaseDate, $loanInterestMethod,
    $loanInterest, $loanInterestFrequency, $loanDurationNumber, $loanDuration, $repaymentCycle);

// Print the loan schedule as an HTML table
echo '<table border="1">';
echo '<tr><th>Date</th><th>Principal Payment</th><th>Interest Payment</th><th>Total Payment</th><th>Remaining Principal</th></tr>';

foreach ($schedule as $payment) {
    echo '<tr>';
    echo '<td>' . $payment["Date"] . '</td>';
    echo '<td>' . $payment["Principal Payment"] . '</td>';
    echo '<td>' . $payment["Interest Payment"] . '</td>';
    echo '<td>' . $payment["Total Payment"] . '</td>';
    echo '<td>' . $payment["Remaining Principal"] . '</td>';
    echo '</tr>';
}

echo '</table>';
    

 
});
