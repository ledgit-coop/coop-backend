<?php

namespace App\Helpers;

use App\Constants\TransactionType;
use App\Models\LoanSchedule;
use App\Models\Member;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Carbon;

class TransactionHelper {

    public static function makeTransaction(float $amount, string $particular, $type, Carbon $transactionDate ,$extraParams = null) {

        if(in_array($type, TransactionType::LIST)) throw new Exception("Transaction type is not supported.", 1);

        return Transaction::create([
            'transaction_number' => TransactionNumber::generateTransactionNumber(),
            'amount' => $amount,
            'type' => $type,
            'transaction_date' => $transactionDate->format('Y-m-d'),
            'particular' => $particular,
            'parameters' => $extraParams ? json_encode($extraParams) : null,
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
            [
                'member_id' => $member->id
            ]
        );
    }

    public static function makeExpenses(string $particular, Carbon $transactionDate, float $amount) {
        return self::makeTransaction(
            $amount,
            $particular,
            TransactionType::EXPENSE,
            $transactionDate,
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
            [
                "loan_id" => $schedule->loan->id,
                "loan_schedule_id" => $schedule->id,
            ]
        );
    }
}

