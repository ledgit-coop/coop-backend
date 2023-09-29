<?php

namespace App\Helpers;

use App\Constants\LogTypes;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Log;
use App\Models\Member;
use Exception;
use Illuminate\Support\Facades\Auth;

class LogHelper {

    public static function create($type, $content, $module, $module_id, $parent_module, $parent_module_id) {
        return Log::create([
            'type' => $type,
            'content' => $content,
            'model' => $module,
            'model_id' => $module_id,
            'parent_model' => $parent_module,
            'parent_model_id' => $parent_module_id,
            'created_by' => Auth::user()->id,
        ]);
    }


    public static function logLoanCreated(Loan $loan) {
        return self::create(
            LogTypes::SYSTEM,
            'Loan created and status set to ' . $loan->status,
            Loan::class,
            $loan->id,
            Member::class,
            $loan->member->id,
        );
    }

    public static function logLoanStatusChange(Loan $loan) {
        return self::create(
            LogTypes::SYSTEM,
            'Status updated to ' . $loan->status,
            Loan::class,
            $loan->id,
            Member::class,
            $loan->member->id,
        );
    }

    public static function logLoanPayment(LoanSchedule $schedule) {
        return self::create(
            LogTypes::SYSTEM,
            'Payment recorded amounting ' . number_format($schedule->amount_paid, 2),
            LoanSchedule::class,
            $schedule->id,
            Loan::class,
            $schedule->loan->id,
        );
    }
}

