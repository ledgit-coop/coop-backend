<?php

namespace App\Helpers;

use App\Constants\TransactionType;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class TransactionHelper {

    public static function generateTransactionNumber() {

        $currentYear = Carbon::now()->format('ymd');

        $count = Transaction::count();

        $sequence = sprintf('%09d', $count + 1);

        return  'TXN-'.$currentYear . $sequence;
    }

    public static function makeTransaction(float $amount, string $particular, $type, Carbon $transactionDate, $created_by = 'System' ,$extraParams = null) {

        if(!in_array($type, TransactionType::LIST)) throw new Exception("Transaction type is not supported.", 1);

        return Transaction::create([
            'transaction_number' => self::generateTransactionNumber(),
            'amount' => $amount,
            'type' => $type,
            'transaction_date' => $transactionDate->format('Y-m-d'),
            'particular' => $particular,
            'parameters' => $extraParams ? json_encode($extraParams) : null,
            'created_by' => $created_by,
        ]);
    }

    public static function makeMembershipTransaction(Member $member, Carbon $paidDate, float $amount) {
        $name =  $member->full_name;
        $number = $member->member_number;
        return self::makeTransaction(
            $amount,
            "Membership payment made by $name - $number",
            TransactionType::REVENUE,
            $paidDate,
            'System',
            [
                'member_id' => $member->id
            ]
        );
    }

    public static function makeExpenses(string $particular, Carbon $transactionDate, float $amount, User $created_by) {
        return self::makeTransaction(
            $amount,
            $particular,
            TransactionType::EXPENSE,
            $transactionDate,
            $created_by->name,
        );
    }

    public static function makeLoanPayment(LoanSchedule $schedule, Carbon $transactionDate, float $amount) {
        $date = $schedule->due_date;
        $loan_number = $schedule->loan->loan_number;
        return self::makeTransaction(
            $amount,
            "Loan amortization - $date/Loan #: $loan_number",
            TransactionType::PAYMENT,
            $transactionDate,
            'System',
            [
                "loan_id" => $schedule->loan->id,
                "loan_schedule_id" => $schedule->id,
            ]
        );
    }
    
    public static function makeLoanPreTerminationFee(Loan $loan, Carbon $transactionDate, float $amount) {
        $date = $transactionDate->format('Y-m-d');
        $loan_number = $loan->loan_number;
        return self::makeTransaction(
            $amount,
            "Loan Pre-Termination - $date/Loan #: $loan_number",
            TransactionType::REVENUE,
            $transactionDate,
            'System',
            [
                "loan_id" => $loan->id,
            ]
        );
    }
}

