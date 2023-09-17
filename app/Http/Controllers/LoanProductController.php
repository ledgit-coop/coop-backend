<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanProductRequest;
use App\Models\LoanProduct;
use Illuminate\Http\Request;

class LoanProductController extends Controller
{
    public function index(Request $request) {

        $products = LoanProduct::on();
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
 
        if($request->sortField && $request->sortOrder)
            $products->orderBy($request->sortField, $request->sortOrder);
 
        return response()->json($products->paginate($limit));
    }

    public function store(LoanProductRequest $request)
    {
        $data = $request->only([
            'name',
            'default_principal_amount',
            'min_principal_amount',
            'max_principal_amount',
            'disbursed_channel',
            'interest_method',
            'interest_type',
            'loan_interest_period',
            'default_loan_interest',
            'loan_duration_type',
            'default_loan_duration',
            'repayment_cycle',
            'default_number_of_repayments',
            'repayment_mode',
        ]);

        $product = LoanProduct::create($data);

        if($request->loan_product_fees)
            $product->loan_product_fees()->createMany($request->loan_product_fees);

        return response()->json($product);
    }

    public function show(LoanProduct $loanProduct)
    {
        $loanProduct = LoanProduct::with('loan_product_fees.loan_fee_template')->find($loanProduct->id);
        
        return response()->json($loanProduct);
    }

    public function update(LoanProductRequest $request, LoanProduct $loanProduct)
    {
        $data = $request->only([
            'name',
            'default_principal_amount',
            'min_principal_amount',
            'max_principal_amount',
            'disbursed_channel',
            'interest_method',
            'interest_type',
            'loan_interest_period',
            'default_loan_interest',
            'loan_duration_type',
            'default_loan_duration',
            'repayment_cycle',
            'default_number_of_repayments',
            'repayment_mode',
        ]);

        foreach ($data as $key => $value) {
            $loanProduct->{$key} = $value;
        }
        
        $loanProduct->save();

        if($request->loan_product_fees)
        {
            foreach ($request->loan_product_fees as $fee) {
                $loanProduct->loan_product_fees()->updateOrCreate([
                    'loan_fee_template_id' => $fee['loan_fee_template_id']
                ],
                [
                    ...$fee,
                ],);
            }
        }

        return response()->json($loanProduct);
    }

    public function destroy(LoanProduct $loanProduct)
    {
        if($loanProduct->locked) return response()->json(['message' => 'Cannot delete locked loan product.'], 443);

        $loanProduct->delete();
        return response(true);
    }
}
