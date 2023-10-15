<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanFeeTemplateRequest;
use App\Models\LoanFeeTemplate;
use Illuminate\Http\Request;

class LoanFeeTemplateController extends Controller
{
    public function index(Request $request) {

        $fees = LoanFeeTemplate::on();
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $fees->where(function($loans) use($filters) {
                    $loans->orWhere('name', 'like', "%$filters->keyword%");
                });
 
        }
 
        if($request->sortField && $request->sortOrder)
            $fees->orderBy($request->sortField, $request->sortOrder);
 
        return response()->json($fees->paginate($limit));
    }

    public function store(LoanFeeTemplateRequest $request)
    {
        $data = $request->only([
            'name',
            'fee',
            'fee_type',
            'fee_method',
            'enabled',
            'credit_revenue',
            'credit_share_capital',
            'credit_regular_savings',
            'show_to_report',
        ]);

        $product = LoanFeeTemplate::create($data);

        return response()->json($product);
    }

    public function show(LoanFeeTemplate $loanFee)
    {
        return response()->json($loanFee);
    }

    public function update(LoanFeeTemplateRequest $request, LoanFeeTemplate $loanFee)
    {
        $data = $request->only([
            'name',
            'fee',
            'fee_type',
            'fee_method',
            'enabled',
            'credit_revenue',
            'credit_share_capital',
            'credit_regular_savings',
            'show_to_report',
        ]);

        foreach ($data as $key => $value) {
            $loanFee->{$key} = $value;
        }

        $loanFee->save();

        return response()->json($loanFee);
    }

    public function toggle(LoanFeeTemplate $loanFee)
    {
        $loanFee->enabled = !$loanFee->enabled;
        $loanFee->save();
        
        return response(true);
    }

    public function destroy(LoanFeeTemplate $loanFee)
    {
        $loanFee->delete();
        return response(true);
    }
}
