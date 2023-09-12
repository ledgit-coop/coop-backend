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
        'loan_interest',
        'loan_interest_period',
        'loan_duration',
        'repayment_cycle',
        'number_of_repayments',
    ];
}
