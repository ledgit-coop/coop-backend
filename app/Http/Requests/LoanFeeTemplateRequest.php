<?php

namespace App\Http\Requests;

use App\Constants\LoanFeeMethod;
use App\Constants\LoanFeeType;
use Illuminate\Foundation\Http\FormRequest;

class LoanFeeTemplateRequest extends FormRequest
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
            'fee' => 'nullable|numeric|between:0.00,9999999.99',
            'fee_type' => ['nullable', 'string', 'in:' . implode(',', LoanFeeType::LIST)],
            'fee_method' => ['nullable', 'string', 'in:' . implode(',', LoanFeeMethod::LIST)],
            'enabled' => 'nullable|boolean',
            'credit_revenue' => 'nullable|boolean',
            'credit_share_capital' => 'nullable|boolean',
            'credit_regular_savings' => 'nullable|boolean',
        ];
    }
}
