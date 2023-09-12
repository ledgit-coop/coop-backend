<?php

namespace App\Http\Controllers;

use App\Helpers\MemberHelper;
use App\Http\Requests\MemberRequest;
use App\Models\Account;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\MemberAccount;
use App\Models\WorkIndustry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UtilityController extends Controller
{
    public function memberDropdown() {
        return Member::select('first_name', 'surname','middle_name', 'id')->get()->map(function($member){
            return [
                'value' => $member->id,
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


    public function memberAccountDropdown($member_id) {
        $member = Member::findOrFail($member_id);
        return MemberAccount::where('member_id', $member->id)->with('account')->get()->map(function($data){
            return [
                'value' => $data->id,
                'label' => $data->account->name,
            ];
        });
    }
}
