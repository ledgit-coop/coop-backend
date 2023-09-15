<?php

namespace App\Http\Controllers;

use App\Constants\MemberLoanStatus;
use App\Helpers\LoanHelper;
use App\Http\Requests\LoanApplicationRequest;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

class LoanController extends Controller
{
    public function index(Request $request) {

        $loans = Loan::on();
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;

        if($request->member_id)
            $loans->where('member_id', $request->member_id);

        if(!empty($filters)) {
            if(isset($filters->status))
                $loans->where('status', $filters->status);
            if(isset($filters->loan_product_id))
                $loans->where('loan_product_id', $filters->loan_product_id);
            if(isset($filters->year))
                $loans->whereYear('applied_date', $filters->year);
        }
        if($request->sortField && $request->sortOrder)
            $loans->orderBy($request->sortField, $request->sortOrder);


        $loans->with('loanProduct');
        return response()->json($loans->paginate($limit));
    }

    public function store(LoanApplicationRequest $request)
    {
        $data = $request->only([
            'email',
            'member_id',
            'loan_product_id',
            'contact_number',
            'age',
            'civil_status',
            'present_address',
            'home_address',
            'valid_id',
            'tin_number',
            'number_of_children',
            'application_type',
            'employer_name',
            'occupation',
            'work_address',
            'work_industry',
            'loan_purpose',
            'salary_range',
            'applied_amount',
            'principal_amount',
            'disbursed_channel',
            'interest_method',
            'interest_type',
            'loan_interest',
            'loan_interest_period',
            'loan_duration',
            'repayment_cycle',
            'number_of_repayments',
            're_payment_mode',
            're_payment_method',
            'member_account_id',
            'applied_date',
            'loan_duration_type',
            'guarantor_first_id',
            'guarantor_second_id',
            'repayment_mode',
            'released_date',
        ]);

        $data['loan_number'] = LoanHelper::generateUniqueTransactionNumber();

        Log::info(json_encode($data));
        $loan = Loan::create($data);

        return response()->json($loan);
    }

    public function show(Loan $loan)
    {
        $loan = Loan::where('id', $loan->id)
                ->with('loanProduct')
                ->with('member')
                ->with('guarantor_first')
                ->with('guarantor_second')
                ->with('member_account.account')
                ->firstOrFail(); 

        return response()->json($loan);
    }

    public function update(LoanApplicationRequest $request, Loan $loan)
    {
        $data = $request->only([
            'email',
            'loan_product_id',
            'contact_number',
            'age',
            'civil_status',
            'present_address',
            'home_address',
            'valid_id',
            'tin_number',
            'number_of_children',
            'application_type',
            'employer_name',
            'occupation',
            'work_address',
            'work_industry',
            'loan_purpose',
            'salary_range',
            'applied_amount',
            'principal_amount',
            'disbursed_channel',
            'interest_method',
            'interest_type',
            'loan_interest',
            'loan_interest_period',
            'loan_duration',
            'repayment_cycle',
            'number_of_repayments',
            're_payment_mode',
            're_payment_method',
            'member_account_id',
            'applied_date',
            'loan_duration_type',
            'guarantor_first_id',
            'guarantor_second_id',
            'repayment_mode',
            'released_date',
        ]);

        foreach ($data as $key => $value) {
            $loan->{$key} = $value;
        }

        $loan->save();

        return response()->json($loan);
    }


    public function updateStatus(Request $request, Loan $loan)
    {
        $this->validate($request,[
            'status' => 'required|in:'. implode(",", MemberLoanStatus::LIST),
        ]);

        if($loan->released) return response()->json(['message' => 'Cannot update status of a released loan.'], 422);

        if($request->status === MemberLoanStatus::RELEASED)
            $loan->released = true;

        $loan->status = $request->status;
        $loan->save();
        
        return response()->json($loan);
    }

    public function destroy(Loan $loan)
    {
        if($loan->released) return response()->json(['message' => 'Cannot delete released loan.'], 422);

        $loan->delete();

        return response(true);
    }

    public function activeLoans(Member $member) {
        
        $loans = Loan::where('member_id', $member->id);
        
        $loans = $loans->withCasts([
            'widget_data' => 'json'
        ]);

        return response()->json($loans->get());
    }
}
