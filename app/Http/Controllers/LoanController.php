<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddAccountTransactionRequest;
use App\Http\Requests\LoanApplicationRequest;
use App\Models\Loan;
use App\Models\MemberAccount;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function store(LoanApplicationRequest $request)
    {
        $data = $request->only([
            "member_id",
            "loan_product_id",
            "contact_number",
            "age",
            "civil_status",
            "present_address",
            "home_address",
            "valid_id",
            "tin_number",
            "number_of_children",
            "application_type",
            "employer_name",
            "occupation",
            "work_address",
            "work_industry",
            "loan_purpose",
            "salary_range",
            "applied_amount",
            "principal_amount",
            "disbursed_channel",
            "interest_method",
            "interest_type",
            "loan_interest",
            "loan_interest_period",
            "loan_duration",
            "repayment_cycle",
            "number_of_repayments",
            "re_payment_mode",
            "re_payment_method",
            "member_account_id",
        ]);

        $loan = Loan::create($data);

        return response()->json($loan);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function addAccount($member_number, $account_id) {
        
        return response('Account created.');
    }

    public function attendedOrientation($member_number) {
        

        return response('Account updated.');
    }
    

    public function addAccountTransaction(AddAccountTransactionRequest $request, MemberAccount $member_account) {
        
       
        return response('Transaction created.');
    }
}
