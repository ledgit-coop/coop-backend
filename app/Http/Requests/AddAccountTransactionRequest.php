<?php

namespace App\Http\Requests;

use App\Constants\ActionTransaction;
use Illuminate\Foundation\Http\FormRequest;

class AddAccountTransactionRequest extends FormRequest
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
            'transaction_date' => 'required|date|date_format:Y-m-d',
            'transaction_type' => 'required|in:'. implode(',', ActionTransaction::LIST),
            'amount' => 'required',
            'member_account_id' => 'required_if:transaction_type,'. implode(',', [ActionTransaction::DepositSavings, ActionTransaction::WithdrawSavings]),
            'particular' => 'required_if:transaction_type,'. implode(',', [ActionTransaction::DepositSavings, ActionTransaction::WithdrawSavings]),
        ];
    }
}
