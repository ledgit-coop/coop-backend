<?php

namespace App\Http\Controllers;

use App\Constants\AccountType;
use App\Constants\MemberLoanStatus;
use App\Constants\MemberStatus;
use App\Constants\TransactionType;
use App\Models\AccountTransaction;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Member;
use App\Models\MemberAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function counts() {

        $current_date = Carbon::now();

        $members = Member::where('status', MemberStatus::ACTIVE)->count();

        $overdue_loans = LoanSchedule::where('overdue', true)->count();
        $overdue_loans_current_month = LoanSchedule::where('overdue', true)->whereMonth('due_date', $current_date->month)->count();

        $shared_capital_total = MemberAccount::whereHas('account', function($account) {
            $account->where('type', AccountType::SHARE_CAPITAL);
        })->sum('balance');
        $shared_capital_current_month = AccountTransaction::whereHas('member_account', function($account) {
            $account->whereHas('account', function($account) {
                $account->where('type', AccountType::SHARE_CAPITAL);
            });
        })
        ->whereMonth('transaction_date', $current_date->month)
        ->sum('amount');

        $loan_released = Loan::where('released', true)->count();
        $loan_released_current_month = Loan::where('released', true)->whereMonth('released_date', $current_date->month)->count();

        $last_week_start_date = (new Carbon())->subWeek()->startOfWeek();
        $last_week_end_date = (new Carbon())->subWeek()->endOfWeek();
        
        $new_registered_since_last_week = Member::whereBetween('member_at',[
            $last_week_start_date->format('Y-m-d'),
            $last_week_end_date->format('Y-m-d')
        ])->count();

        return response()->json([
            'member_count' => $members,

            'overdue_loan_count' => $overdue_loans,
            'overdue_loans_current_month' => $overdue_loans_current_month,

            'shared_capital_total' => $shared_capital_total,
            'shared_capital_current_month' => $shared_capital_current_month,

            'loan_released_count' => $loan_released,
            'loan_released_current_month' => $loan_released_current_month,

            'new_registered_since_last_week' => $new_registered_since_last_week,
        ]);
    }

    public function cashFlow(Request $request) {

        $current_date = Carbon::now();
        $year = $request->year ?? $current_date->year;

        $share_capital = AccountTransaction::select('amount', DB::raw("month(transaction_date) as month"))
            ->whereHas('member_account', function($account) {
                $account->whereHas('account', function($account) {
                    $account->where('type', AccountType::SHARE_CAPITAL);
                });
            })
            ->whereYear('transaction_date', $year)
            ->get();

        $expenses = Transaction::select('amount', DB::raw("month(transaction_date) as month"))
            ->where('type', TransactionType::EXPENSE)->whereYear('transaction_date', $year)->get();

        $revenue = Transaction::select('amount', DB::raw("month(transaction_date) as month"))
            ->where('type', TransactionType::REVENUE)->whereYear('transaction_date', $year)->get();

        $cashflow = [];
        for ($i=1; $i <= 12; $i++) { 
            $cashflow[] = [
                'month' => Carbon::now()->setMonth($i)->format("M"),
                'year' => $year,
                'flow' => [
                    'share_capital' => $share_capital->where('month', $i)->sum('amount'),
                    'expenses' => $expenses->where('month', $i)->sum('amount'),
                    'revenue' => $revenue->where('month', $i)->sum('amount'),
                ]
            ];
        }

        return response()->json($cashflow);
    }

    public function recentLoans() {

        $current_date = Carbon::now();
        $prev_date = $current_date->clone()->subDays(10); // Last 10 days

        $loans = Loan::whereBetween('created_at',[$prev_date->format('Y-m-d'), $current_date->format('Y-m-d')])
        ->with('member')
        ->with('loan_product')
        ->get();

        return response()->json($loans);
    }
}
