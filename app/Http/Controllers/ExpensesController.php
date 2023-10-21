<?php

namespace App\Http\Controllers;

use App\Constants\Pagination;
use App\Constants\TransactionType;
use App\Helpers\TransactionHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $expenses = Transaction::on();

        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;

        $expenses->with('transaction_sub_type');
        $expenses->where('type', TransactionType::EXPENSE);

        if(!empty($filters)) {

            if(isset($filters->transaction_sub_type_id))
                $expenses->where('transaction_sub_type_id', $filters->transaction_sub_type_id);

            if(isset($filters->keyword))
                $expenses->where('particular', 'like', "%$filters->keyword%");
        }
        if($request->sortField && $request->sortOrder)
            $expenses->orderBy($request->sortField, $request->sortOrder);
        else
            $expenses->orderBy('created_at', 'desc');

        return response()->json($expenses->paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'nullable|numeric|between:0.00,9999999.99',
            'particular' => 'required|string',
            'transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        $expense = TransactionHelper::makeExpenses(
            $request->particular,
            new Carbon($request->date),
            $request->amount,
            Auth::user()
        );

        $expense->transaction_sub_type_id = $request->transaction_sub_type_id;
        $expense->saveQuietly();

        return response()->json($expense);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $expense)
    {
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $expense)
    {
        $this->validate($request, [
            'amount' => 'nullable|numeric|between:0.00,9999999.99',
            'transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
            'particular' => 'required|string',
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        $expense->amount = $request->amount;
        $expense->particular = $request->particular;
        $expense->transaction_date = $request->date;
        $expense->transaction_sub_type_id = $request->transaction_sub_type_id;

        $expense->save();
        
        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $expense)
    {
        $expense->delete();

        return response(true);
    }
}
