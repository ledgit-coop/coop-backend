<?php

namespace App\Http\Controllers;

use App\Helpers\LoanCalculator;
use App\Http\Requests\LoanCalculatorRequest;
use App\Models\Account;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\MemberAccount;
use App\Models\MemberBeneficiary;
use App\Models\WorkIndustry;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function memberDropdown() {
        return Member::select('first_name', 'surname','middle_name', 'id')->get()->map(function($member){
            return [
                'value' => "$member->id",
                'label' => $member->full_name,
            ];
        });
    }

    public function accountDropdown() {
        return Account::select('id', 'name')->get()->map(function($data){
            return [
                'value' => $data->id,
                'label' => $data->name,
            ];
        });
    }

    public function workIndustryDropdown() {
        return WorkIndustry::select('name')->get()->map(function($data){
            return [
                'value' => $data->name,
                'label' => $data->name,
            ];
        });
    }

    public function loanProductions() {
        return LoanProduct::all()->map(function($data){
            return [
                'value' => $data->id,
                'label' => $data->name,
            ];
        });
    }


    public function memberAccountDropdown(Request $request, $member_id) {
        $member = Member::findOrFail($member_id);

        $accounts = MemberAccount::where('member_id', $member->id)->with('account');

        if(!empty($request->type))
            $accounts->whereHas('account', function($account) use($request) {
                $account->where('type', $request->type);
            });

        $accounts = $accounts->get()->map(function($data){
            return [
                'value' => $data->id,
                'label' => $data->account->name,
            ];
        });


        return response()->json($accounts);
    }

    public function guarantorDropdown() {
        $guarantors = LoanGuarantor::on();

        $guarantors = $guarantors->get()->map(function($data){
            return [
                'value' => $data->id,
                'label' => $data->full_name,
                'disabled' => rand(0,1),
                'extra' => [
                    'guarantor_twice' => false,
                ]
            ];
        });

        return response()->json($guarantors);
    }


    public function loanCalculator(LoanCalculatorRequest $request) {
        $calculator = new LoanCalculator();
        $result = $calculator->generateSchedule(
            $request->principal_amount, 
            $request->loan_interest, 
            $request->loan_duration, 
            $request->interest_method, 
            $request->number_of_repayments, 
            $request->repayment_cycle, 
            $request->loan_duration_type, 
            $request->loan_interest_period, 
            $request->released_date,
        );

        return response()->json($result);
    }

    public function memberAccountHolder(Member $member) {

        $beneficiaries = $member->beneficiaries->map(function($member){
            return [
                'value' => $member->name,
                'label' => empty(str_replace(" ", "", $member->name)) ? '----' : $member->name,
            ];
        })->toArray();
        
        return response()->json(array_merge([['value' => $member->full_name,'label' => $member->full_name]],$beneficiaries));
    }
}
