<?php

namespace App\Helpers;

use App\Constants\LogTypes;
use App\Constants\UserType;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Log;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LogHelper {

    public static function create($type, $content, $module, $module_id, $parent_module, $parent_module_id) {
        $user = Auth::user();

        if(!$user)
            $user = User::where('type', UserType::SYSTEM)->first();

        return Log::create([
            'type' => $type,
            'content' => $content,
            'model' => $module,
            'model_id' => $module_id,
            'parent_model' => $parent_module,
            'parent_model_id' => $parent_module_id,
            'created_by' => $user->id,
        ]);
    }


    public static function logMembeshipPayment(Member $member, Transaction $transaction) {
        $date = (new Carbon($transaction->transaction_date))->format('Y-m-d');
        $amount = number_format($transaction->amount, 2);
        return self::create(
            LogTypes::SYSTEM,
            "Membership payment $date amounting $amount with transaction #: $transaction->transaction_number",
            Member::class,
            $member->id,
            null,
            null,
        );
    }

    public static function logOrientationPayment(Member $member, Transaction $transaction) {
        $date = (new Carbon($transaction->transaction_date))->format('Y-m-d');
        $amount = number_format($transaction->amount, 2);
        return self::create(
            LogTypes::SYSTEM,
            "Orientation payment $date amounting $amount with transaction #: $transaction->transaction_number",
            Member::class,
            $member->id,
            null,
            null,
        );
    }


    public static function logLoanCreated(Loan $loan) {
        return self::create(
            LogTypes::SYSTEM,
            "Loan ($loan->loan_number) created",
            Loan::class,
            $loan->id,
            Member::class,
            $loan->member->id,
        );
    }

    public static function logLoanStatusChange(Loan $loan) {
        return self::create(
            LogTypes::SYSTEM,
            "Loan ($loan->loan_number) status updated to $loan->status",
            Loan::class,
            $loan->id,
            Member::class,
            $loan->member->id,
        );
    }

    public static function logLoanPayment(LoanSchedule $schedule) {
        $loan = $schedule->loan;
        return self::create(
            LogTypes::SYSTEM,
            "Loan ($loan->loan_number) payment recorded amounting " . number_format($schedule->amount_paid, 2),
            LoanSchedule::class,
            $schedule->id,
            Loan::class,
            $schedule->loan->id,
        );
    }

    public static function logLoanScheduleUpdate(LoanSchedule $schedule) {
        $message = "Loan schedule fields has been updated: ";
        $fieldMessage = [];

        foreach ($schedule->getDirty() as $key => $value) {
            $new = $value;
            $old = $schedule->getOriginal($key);
            $fieldMessage[] = str_replace("_", " ", $key) . " from $old to $new";
        }

        $message .= implode(",", $fieldMessage);

        return self::create(
            LogTypes::SYSTEM,
            $message,
            LoanSchedule::class,
            $schedule->id,
            Loan::class,
            $schedule->loan->id,
        );
    }
}

