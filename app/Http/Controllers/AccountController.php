<?php

namespace App\Http\Controllers;

use App\Constants\AccountMaintainingBalanceCycle;
use App\Constants\AccountMaintainingBalanceMethod;
use App\Constants\AccountType;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request) {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
        
        $accounts = Account::on();
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $accounts->where(function($accounts) use($filters) {
                    $accounts->orWhere('name', 'like', "%$filters->keyword%")
                    ->orWhere('type', 'like', "%$filters->keyword%");
                });
        }

        if($request->sortField && $request->sortOrder)
            $accounts->orderBy($request->sortField, $request->sortOrder);


        return $accounts->paginate($limit);
    }

    public function show(Account $account)
    {
        return response()->json($account);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'key' => 'required|unique:accounts,key',
            'name' => 'required',
            'type' => 'required|in:' . implode(',', AccountType::LIST),
            'earn_interest_per_anum' => 'nullable|numeric|min:0',
            'maintaining_balance' => 'nullable|numeric|min:0',
            'penalty_below_maintaining_method' => 'nullable|in:' . implode(',', AccountMaintainingBalanceMethod::LIST),
            'penalty_below_maintaining' => 'nullable|numeric|min:0',
            'penalty_below_maintaining_cycle' => 'nullable|in:' . implode(',', AccountMaintainingBalanceCycle::LIST),
            'penalty_below_maintaining_duration' => 'nullable|numeric|min:0',
        ]);

        $account = Account::create($validatedData);

        return response()->json($account);
    }

    public function update(Request $request, Account $account)
    {
        $validatedData = $request->validate([
            'key' => 'required|unique:accounts,key,' . $account->id,
            'name' => 'required',
            'type' => 'required|in:' . implode(',', AccountType::LIST),
            'earn_interest_per_anum' => 'nullable|numeric|min:0',
            'maintaining_balance' => 'nullable|numeric|min:0',
            'penalty_below_maintaining_method' => 'nullable|in:' . implode(',', AccountMaintainingBalanceMethod::LIST),
            'penalty_below_maintaining' => 'nullable|numeric|min:0',
            'penalty_below_maintaining_cycle' => 'nullable|in:' . implode(',', AccountMaintainingBalanceCycle::LIST),
            'penalty_below_maintaining_duration' => 'nullable|numeric|min:0',
        ]);

        $account->update($validatedData);

        return response()->json($account);
    }

    public function destroy(Account $account)
    {
        $account->delete();
        
        return response(true);
    }
}
