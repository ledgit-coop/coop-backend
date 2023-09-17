<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'locked',
        'fees',
    ];

    protected $casts = [
        'default_principal_amount' => 'integer',
        'min_principal_amount' => 'integer',
        'max_principal_amount' => 'integer',
        'default_loan_interest' => 'integer',
        'default_loan_duration' => 'integer',
        'default_number_of_repayments' => 'integer',
        'fees' => 'array',
    ];

    public function loan_product_fees() {
        return $this->hasMany(LoanProductFee::class);
    }
}
