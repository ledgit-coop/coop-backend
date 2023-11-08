<?php

namespace App\Http\Controllers;

use App\Constants\AccountType;
use App\Constants\FinancialTypes;
use App\Constants\MemberAccountTransactionType;
use App\Constants\Pagination;
use App\Constants\TransactionType;
use App\Models\AccountTransaction;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
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

        $sub_types = TransactionSubType::get()->map(function($transaction) use($request) {
            $name = strtolower($transaction->name);
            return [
                'name' => "Total $name amount",
                'amount' => $transaction->transactions()->whereBetween('transaction_date', [$request->from, $request->to])->sum('amount')
            ];
        });

        $total_collected_interest_amount = LoanSchedule::where('paid', true)
            ->whereBetween('due_date', [$request->from, $request->to])
            ->sum('interest_amount');

        $total_collected_penalty_amount = LoanSchedule::where('paid', true)
            ->whereBetween('due_date', [$request->from, $request->to])
            ->sum('penalty_amount');

        $total_collected_amortization = LoanSchedule::where('paid', true)
            ->whereBetween('due_date', [$request->from, $request->to])
            ->sum('amount_paid');
            
        $total_loan_released_amount = Loan::whereBetween('released_date', [$request->from, $request->to])
            ->where('released', true)
            ->sum('principal_amount');

        $total_loans_collection = LoanSchedule::select([
            DB::raw("sum(due_amount) as total_due_amount"),
            DB::raw("sum(amount_paid) as total_amount_paid"),
        ])->whereHas('loan', function($loan) {
            $loan->where('released', true);
        })
        ->whereBetween('due_date', [$request->from, $request->to])
        ->where('paid', false)->first();
        $total_loans_collection = $total_loans_collection ? ($total_loans_collection->total_due_amount - $total_loans_collection->total_amount_paid) : 0;

        // ------ ALL TIME REPORTS --------

        $all_time_total_loans_collection = LoanSchedule::whereHas('loan', function($loan) {
            $loan->where('released', true);
        })
        ->where('paid', false)->sum('due_amount');

        $all_time_total_loans_collected = LoanSchedule::whereHas('loan', function($loan) {
                $loan->where('released', true);
            })
            ->where('paid', true)
            ->sum('amount_paid');

        $all_time_share_capital_total_amount = AccountTransaction::where('type', MemberAccountTransactionType::SHARE_CAPITAL)
        ->whereHas('member_account', function ($acc) {
            $acc->whereHas('member');
        })
        ->sum('amount');

        $all_time_total_expenses_amount = Transaction::whereHas('transaction_sub_type', function($type) {
                $type->where('type', FinancialTypes::EXPENSES);
            })
            ->sum('amount');

        $all_time_total_loan_released_amount = Loan::where('released', true)
            ->sum('principal_amount');

        $all_time_interest_loan_interest = Loan::where('released', true)
            ->sum('interest_amount');

        // ------  END ALL TIME REPORT  ------

        return response()->json([
            'total_share_capital_amount' => $share_capital_total_amount,
            'total_savings_account_amount' => $total_savings_account_amount,
            'all_time_total_expenses_amount' => $all_time_total_expenses_amount,
            'total_loan_released_amount' => $total_loan_released_amount,
            'total_collected_interest_amount' => $total_collected_interest_amount,
            'total_collected_penalty_amount' => $total_collected_penalty_amount,
            'total_collected_amortization' => $total_collected_amortization,
            'total_loans_collection' => $total_loans_collection,
            'total_all_fees' => [], // @TODO: Remove
            'total_sub_types' => $sub_types,

            'all_time_total_loans_collection' => $all_time_total_loans_collection,
            'all_time_share_capital_total_amount' => $all_time_share_capital_total_amount,
            'all_time_total_loan_released_amount' => $all_time_total_loan_released_amount,
            'all_time_interest_loan_interest' => $all_time_interest_loan_interest,
            'all_time_total_loans_collected' => $all_time_total_loans_collected,
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
            ->with('transaction_sub_type')
            ->whereBetween('transaction_date', [$request->filters['from'], $request->filters['to']]);
        
        if(isset($request->filters['transaction_sub_type_id']) && !empty($request->filters['transaction_sub_type_id']))
            $expenses->where('transaction_sub_type_id', $request->filters['transaction_sub_type_id']);

        $expenses = $expenses->paginate($limit);

        return response()->json($expenses);
    }

    public function revenues(Request $request) {
             
        $this->validate($request, [
            'filters.from' => 'required|date|date_format:Y-m-d',
            'filters.to' => 'required|date|date_format:Y-m-d',
            'transaction_sub_type_id' => 'nullable',
        ]);

        $limit = $request->limit ?? Pagination::PER_PAGE;

        $revenues = Transaction::whereHas('transaction_sub_type', function($subType) {
                $subType->where('type', FinancialTypes::REVENUES);
            })
            ->with('transaction_sub_type')
            ->whereNotNull('transaction_sub_type_id')
            ->whereBetween('transaction_date', [$request->filters['from'], $request->filters['to']]);
        
        if(isset($request->filters['transaction_sub_type_id']) && !empty($request->filters['transaction_sub_type_id']))
            $revenues->where('transaction_sub_type_id', $request->filters['transaction_sub_type_id']);

        $revenues = $revenues->paginate($limit);

        return response()->json($revenues);
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
        ->where('transactions.type', TransactionType::REVENUE)
        ->whereBetween('transaction_date', [$request->from, $request->to])
        ->groupBy("transactions.transaction_sub_type_id")
        ->get();


        $total_loans_collected = LoanSchedule::select(
                DB::raw("sum(penalty_amount) as penalty_amount"),
                DB::raw("sum(interest_amount) as interest_amount")
            )
            ->whereBetween('due_date', [$request->from, $request->to])
            ->where('paid', true)
            ->first();


        return response()->json([
            ...$revenues,
            ...[
                [
                    'name' => 'Collected Loan Penalties',
                    'amount' => $total_loans_collected->penalty_amount,
                ],
                [
                    'name' => 'Collected Loan Interest',
                    'amount' => $total_loans_collected->interest_amount,
                ]
            ]
        ]);
    }
}
