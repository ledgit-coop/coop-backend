<?php

namespace App\Http\Controllers;

use App\Constants\Pagination;
use App\Constants\TransactionType;
use App\Helpers\TransactionHelper;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $revenues = Transaction::on();

        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;

        $revenues->with('transaction_sub_type');
        $revenues->where('type', TransactionType::REVENUE);
        $revenues->whereNotNull('transaction_sub_type_id');

        if(!empty($filters)) {

            if(isset($filters->transaction_sub_type_id))
                $revenues->where('transaction_sub_type_id', $filters->transaction_sub_type_id);

            if(isset($filters->transaction_dates)) {
                $dates = [
                    (new Carbon($filters->transaction_dates[0]))->format('Y-m-d'),
                    (new Carbon($filters->transaction_dates[1]))->format('Y-m-d'),
                ];

                $revenues->whereBetween('transaction_date', $dates);
            }

            if(isset($filters->keyword))
                $revenues->where('particular', 'like', "%$filters->keyword%");
        }
        if($request->sortField && $request->sortOrder)
            $revenues->orderBy($request->sortField, $request->sortOrder);
        else
            $revenues->orderBy('created_at', 'desc');

        return response()->json($revenues->paginate($limit));
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

        $revenue = TransactionHelper::makeIncome(
            $request->particular,
            new Carbon($request->date),
            $request->amount,
            Auth::user()
        );

        $revenue->transaction_sub_type_id = $request->transaction_sub_type_id;
        $revenue->saveQuietly();

        return response()->json($revenue);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $revenue)
    {
        return response()->json($revenue);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $revenue)
    {
        $this->validate($request, [
            'amount' => 'nullable|numeric|between:0.00,9999999.99',
            'transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
            'particular' => 'required|string',
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        if($revenue->posted)
            throw new Exception("Cannot edit a posted transaction.", 1);
            

        $revenue->amount = $request->amount;
        $revenue->particular = $request->particular;
        $revenue->transaction_date = $request->date;
        $revenue->transaction_sub_type_id = $request->transaction_sub_type_id;

        $revenue->save();
        
        return response()->json($revenue);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $revenue)
    {
        $revenue->delete();

        return response(true);
    }
}
