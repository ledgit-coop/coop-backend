<?php

namespace App\Http\Controllers;

use App\Constants\AccountType;
use App\Constants\MemberAccountTransactionType;
use App\Constants\Pagination;
use App\Constants\TransactionType;
use App\Models\AccountTransaction;
use App\Models\Loan;
use App\Models\LoanFee;
use App\Models\LoanProduct;
use App\Models\Transaction;
use App\Models\TransactionSubType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function counter(Request $request) {
        
        $this->validate($request, [
            'from' => 'required|date|date_format:Y-m-d',
            'to' => 'required|date|date_format:Y-m-d',
        ]);


        $share_capital_total_amount = AccountTransaction::where('type', MemberAccountTransactionType::SHARE_CAPITAL)
            ->whereHas('member_account', function ($acc) {
                $acc->whereHas('member');
            })
            ->whereBetween('transaction_date', [$request->from, $request->to])
            ->sum('amount');

        $total_savings_account_amount = AccountTransaction::whereHas('member_account', function ($acc) {
                $acc->whereHas('account', function($acc) {
                    $acc->where('type', AccountType::SAVINGS);
                })
                ->whereHas('member');
            })
            ->whereBetween('transaction_date', [$request->from, $request->to])
            ->sum('amount');

        $total_expenses_amount = Transaction::where('type', TransactionType::EXPENSE)
            ->whereBetween('transaction_date', [$request->from, $request->to])
            ->sum('amount');

        $sub_types = TransactionSubType::get()->map(function($transaction) {
            return [
                'name' => "Total $transaction->name amount",
                'amount' => $transaction->transactions()->sum('amount')
            ];
        });

        $total_all_fees = LoanFee::select(['loan_fee_template_id', DB::raw("sum(amount) as total")])
        ->whereHas('loan_fee_template', function($template) {
            $template->where('show_to_report', true);
        })
        ->with('loan_fee_template')
        ->groupBy('loan_fee_template_id')
        ->get()
        ->map(function($fee) {
            return [
                'name' => "Total " . $fee->loan_fee_template->name . " amount",
                'amount' => $fee->total,
            ];
        });

        $total_loan_released_amount = Loan::whereBetween('released_date', [$request->from, $request->to])
            ->where('released', true)
            ->sum('principal_amount');

        return response()->json([
            'total_share_capital_amount' => $share_capital_total_amount,
            'total_savings_account_amount' => $total_savings_account_amount,
            'total_expenses_amount' => $total_expenses_amount,
            'total_loan_released_amount' => $total_loan_released_amount,
            'total_all_fees' => $total_all_fees,
            'total_sub_types' => $sub_types
        ]);
    }

    public function shareCapitals(Request $request) {
             
        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $share_capitals = AccountTransaction::where('type', MemberAccountTransactionType::SHARE_CAPITAL)
        ->whereHas('member_account', function ($acc) {
            $acc->whereHas('member');
        })
        ->with('member_account.member')
        ->whereBetween('transaction_date', [$request->filters['from'], $request->filters['to']])
        ->paginate($limit);

        return response()->json($share_capitals);
    }

    public function expenses(Request $request) {
             
        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $expenses = Transaction::where('type', TransactionType::EXPENSE)
            ->whereBetween('transaction_date', [$request->filters['from'], $request->filters['to']])
            ->paginate($limit);

        return response()->json($expenses);
    }

    public function revenues(Request $request) {
             
        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $expenses = Transaction::where('type', TransactionType::REVENUE)
            ->whereBetween('transaction_date', [$request->filters['from'], $request->filters['to']])
            ->paginate($limit);

        return response()->json($expenses);
    }

    public function loansReleased(Request $request) {
             
        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $loans = Loan::whereBetween('released_date', [$request->filters['from'], $request->filters['to']])
            ->where('released', true)
            ->with('loan_product')
            ->with('member')
            ->paginate($limit);

        return response()->json($loans);
    }

    public function repayments(Request $request) {

        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $repayments = AccountTransaction::where('type', MemberAccountTransactionType::LOAN_PAYMENT)
        ->with('member_account.member')
        ->orderBy('transaction_date', 'desc')
        ->paginate($limit);

        return response()->json($repayments);
    }

    public function savingsAccountTransactions(Request $request) {

        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $transactions = AccountTransaction::whereHas('member_account', function ($acc) {
            $acc->whereHas('account', function($acc) {
                $acc->where('type', AccountType::SAVINGS);
            })
            ->whereHas('member');
        })
        ->with('member_account.account')
        ->with('member_account.member')
        ->whereBetween('transaction_date', [$request->filters['from'], $request->filters['to']])
        ->orderBy('transaction_date', 'desc')
        ->paginate($limit);


        return response()->json($transactions);
    }

    public function loansReleasedChart(Request $request) {
                
        $this->validate($request, [
            'from' => 'required|date|date_format:Y-m-d',
            'to' => 'required|date|date_format:Y-m-d',
        ]);

        $products = LoanProduct::get();
        return response()->json($products->map(function($product) use($request) {

            $loans = $product->loans()
                ->whereBetween('released_date', [$request->from, $request->to]);
            return [
                'name' => $product->name,
                'count' => $loans->where('released', true)->count(),
            ];
        }));
    }

    public function revenueChart(Request $request) {
          
        $this->validate($request, [
            'from' => 'required|date|date_format:Y-m-d',
            'to' => 'required|date|date_format:Y-m-d',
        ]);
        
        $revenues = Transaction::select(
            DB::raw("case when transaction_sub_type_id is null then 'Others' else transaction_sub_types.name end as `name`"),
            DB::raw("sum(amount) as amount")
        )
        ->leftJoin('transaction_sub_types', 'transaction_sub_types.id', '=', 'transactions.transaction_sub_type_id')
        ->where('type', TransactionType::REVENUE)
        ->whereBetween('transaction_date', [$request->from, $request->to])
        ->groupBy("transactions.transaction_sub_type_id")
        ->get();

        return response()->json($revenues);
    }
}
