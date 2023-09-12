<?php

namespace App\Http\Requests;

use App\Constants\LoanDisbursementChannel;
use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanInterestType;
use App\Constants\LoanRepaymentCycle;
use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'member_account_id'  => 'required|exists:member_accounts,id',
            'contact_number' => 'nullable|string',
            'age' => 'nullable|integer',
            'civil_status' => 'nullable|string',
            'present_address' => 'nullable|string',
            'home_address' => 'nullable|string',
            'valid_id' => 'nullable|string',
            'tin_number' => 'nullable|string',
            'number_of_children' => 'nullable|integer',
            'application_type' => 'nullable|string',
            'employer_name' => 'nullable|string',
            'occupation' => 'nullable|string',
            'work_address' => 'nullable|string',
            'work_industry' => 'nullable|string',
            'loan_purpose' => 'nullable|string',
            'salary_range' => 'nullable|string',
            'applied_amount' => 'nullable|numeric|between:0.01,9999999.99',
            'principal_amount' => 'nullable|numeric|between:0.01,9999999.99',
            'disbursed_channel' => ['nullable', 'string', 'in:' . implode(',', LoanDisbursementChannel::LIST)],
            'interest_method' => ['nullable', 'string', 'in:' . implode(',', LoanInterestMethod::LIST)],
            'interest_type' => ['nullable', 'string', 'in:' . implode(',', LoanInterestType::LIST)],
            'loan_interest' => 'nullable|numeric|between:0.01,999.99',
            'loan_interest_period' => ['nullable', 'string', 'in:' . implode(',', LoanInterestPeriod::LIST)],
            'loan_duration' => 'nullable|numeric|between:0.01,999.99',
            'loan_duration_type' => ['nullable', 'string', 'in:' . implode(',', LoanDurationPeriod::LIST)],
            'repayment_cycle' => ['nullable', 'string', 'in:' . implode(',', LoanRepaymentCycle::LIST)],
            'number_of_repayments' => 'nullable|integer',
            'repayment_mode' => 'nullable|string',
        ];
    }
}
