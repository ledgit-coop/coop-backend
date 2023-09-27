<?php

namespace App\Http\Requests;

use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanRepaymentCycle;
use Illuminate\Foundation\Http\FormRequest;

class LoanCalculatorRequest extends FormRequest
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
            'principal_amount' => 'required|numeric|between:0.00,9999999.99',
            'loan_interest' => 'required|numeric|between:0.00,999.99',
            'loan_duration' => 'required|numeric|between:0.00,999.99',
            'interest_method' => ['required', 'string', 'in:' . implode(',', LoanInterestMethod::LIST)],
            'number_of_repayments' => 'required|integer',
            'repayment_cycle' => ['required', 'string', 'in:' . implode(',', LoanRepaymentCycle::LIST)],
            'loan_duration_type' => ['required', 'string', 'in:' . implode(',', LoanDurationPeriod::LIST)],
            'loan_interest_period' => ['required', 'string', 'in:' . implode(',', LoanInterestPeriod::LIST)],
            'released_date' => 'required|date|date_format:Y-m-d',
            'next_payroll_date' => 'nullable|date|date_format:Y-m-d',
            'fees.*.loan_fee_template_id' => 'nullable|numeric|between:0.00,999.99',
            'fees.*.fee' => 'nullable',
        ];
    }
}
