<?php

namespace App\Http\Requests;

use App\Constants\LoanDisbursementChannel;
use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanInterestType;
use App\Constants\LoanRepaymentCycle;
use Illuminate\Foundation\Http\FormRequest;

class LoanProductRequest extends FormRequest
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
            'name' => 'nullable|string',
            'default_principal_amount' => 'nullable|numeric|between:0.00,9999999.99',
            'min_principal_amount' => 'nullable|numeric|between:0.00,9999999.99',
            'max_principal_amount' => 'nullable|numeric|between:0.00,9999999.99',
            'disbursed_channel' => ['nullable', 'string', 'in:' . implode(',', LoanDisbursementChannel::LIST)],
            'interest_method' => ['nullable', 'string', 'in:' . implode(',', LoanInterestMethod::LIST)],
            'interest_type' => ['nullable', 'string', 'in:' . implode(',', LoanInterestType::LIST)],
            'loan_interest_period' => ['nullable', 'string', 'in:' . implode(',', LoanInterestPeriod::LIST)],
            'default_loan_interest' => 'nullable|numeric|between:0.00,9999999.99',
            'default_loan_duration' => 'nullable|numeric|between:0.00,999.99',
            'loan_duration_type' => ['nullable', 'string', 'in:' . implode(',', LoanDurationPeriod::LIST)],
            'repayment_cycle' => ['nullable', 'string', 'in:' . implode(',', LoanRepaymentCycle::LIST)],
            'number_of_repayments' => 'nullable|integer',
            'repayment_mode' => 'nullable|string',
            'loan_product_fees.*.loan_fee_template_id' => 'nullable|integer',
            'loan_product_fees.*.fee' => 'nullable|integer',
            'penalty' => 'nullable|numeric|between:0.00,9999999.99',
            'penalty_grace_period' => 'nullable|numeric|between:0.00,9999999.99',
            'penalty_method' => 'nullable|string',
            'penalty_duration' => 'nullable|string',            
            'pre_termination_panalty' => 'nullable|numeric|between:0.00,9999999.99',
            'pre_termination_panalty_method' => 'nullable|string',

            'disbursement_transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
            'principal_transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
            'interest_transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
            'penalty_transaction_sub_type_id' => 'required|exists:transaction_sub_types,id',
        ];
    }
}
