<?php

namespace App\Http\Controllers;

use App\Constants\AccountType;
use App\Constants\FinancialTypes;
use App\Constants\MemberAccountTransactionType;
use App\Constants\MemberStatus;
use App\Constants\TransactionType;
use App\Models\AccountTransaction;
use App\Models\Loan;
use App\Models\LoanProduct;
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

        $overdue_loans = LoanSchedule::where('overdue', true)->where('paid', false)->count();
        $overdue_loans_current_month = LoanSchedule::where('overdue', true)
            ->where('paid', false)
            ->whereMonth('due_date', $current_date->month)
            ->count();

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
            ->whereHas('transaction_sub_type', function($subType) {
                $subType->where('type', FinancialTypes::EXPENSES);
            })
            ->whereYear('transaction_date', $year)->get();

        $revenue = Transaction::select('amount', DB::raw("month(transaction_date) as month"))
            ->whereHas('transaction_sub_type', function($subType) {
                $subType->where('type', FinancialTypes::REVENUES);
            })
            ->whereYear('transaction_date', $year)
            ->get();

        $total_loans_collected = LoanSchedule::select(
                DB::raw("sum(penalty_amount) as penalty_amount"),
                DB::raw("sum(interest_amount) as interest_amount"),
                DB::raw("month(due_date) as month")
            )
            ->whereYear('due_date', $year)
            ->where('paid', true)
            ->groupBy(DB::raw("MONTH(due_date)"))
            ->get();

        $cashflow = [];
        
        for ($i=1; $i <= 12; $i++) { 
            $cashflow[] = [
                'month' => Carbon::now()->setMonth($i)->format("M"),
                'year' => $year,
                'flow' => [
                    'share_capital' => $share_capital->where('month', $i)->sum('amount'),
                    'expenses' => $expenses->where('month', $i)->sum('amount'),
                    'revenue' => $revenue->where('month', $i)->sum('amount') + 
                        (
                            $total_loans_collected->where('month', $i)->sum('penalty_amount') + 
                            $total_loans_collected->where('month', $i)->sum('interest_amount')
                        )
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

    public function recentPayments() {

        $recent_payments = AccountTransaction::where('type', MemberAccountTransactionType::LOAN_PAYMENT)
        ->orderBy('transaction_date', 'desc')
        ->limit(10)
        ->get();

        return response()->json($recent_payments);
    }

    public function activeProductLoans() {

        $products = LoanProduct::get();
        return response()->json($products->map(function($product) {
            return [
                'name' => $product->name,
                'interest' => $product->default_loan_interest,
                'period' => $product->loan_interest_period,
                'count' => $product->loans()->count(),
            ];
        }));
    }
}
