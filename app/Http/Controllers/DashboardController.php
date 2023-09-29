<?php

namespace App\Http\Controllers;

use App\Constants\MemberLoanStatus;
use App\Constants\MemberStatus;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Member;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function counts() {
        $members = Member::where('status', MemberStatus::ACTIVE)->count();
        $overdue_loans = LoanSchedule::where('overdue', true)->count();
        $for_release_loans = Loan::where('status', MemberLoanStatus::APPROVED)->count();
        $loan_released = Loan::where('status', MemberLoanStatus::APPROVED)->count();

        $last_week_start_date = (new Carbon())->subWeek()->startOfWeek();
        $last_week_end_date = (new Carbon())->subWeek()->endOfWeek();
        
        $new_registered_since_last_week = Member::whereBetween('member_at',[
            $last_week_start_date->format('Y-m-d'),
            $last_week_end_date->format('Y-m-d')
        ])->count();

        return response()->json([
            'member_count' => $members,
            'overdue_loan_count' => $overdue_loans,
            'for_release_loan_count' => $for_release_loans,
            'loan_released_count' => $loan_released,
            'new_registered_since_last_week' => $new_registered_since_last_week,
        ]);
    }
}
