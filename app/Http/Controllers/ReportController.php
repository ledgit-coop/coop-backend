<?php

namespace App\Http\Controllers;

use App\Constants\AccountTransactionType;
use App\Constants\AccountType;
use App\Constants\MemberAccountTransactionType;
use App\Constants\TransactionType;
use App\Models\AccountTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;

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

        return response()->json([
            'total_share_capital_amount' => $share_capital_total_amount,
            'total_savings_account_amount' => $total_savings_account_amount,
            'total_expenses_amount' => $total_expenses_amount,
        ]);
    }
}
