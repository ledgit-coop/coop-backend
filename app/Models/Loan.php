<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'loan_product_id',
        'member_account_id',
        'status',
        'contact_number',
        'age',
        'civil_status',
        'present_address',
        'home_address',
        'valid_id',
        'tin_number',
        'number_of_children',
        'application_type',
        'employer_name',
        'occupation',
        'work_address',
        'work_industry',
        'loan_purpose',
        'salary_range',
        'applied_amount',
        'principal_amount',
        'disbursed_channel',
        'interest_method',
        'interest_type',
        'loan_interest',
        'loan_interest_period',
        'loan_duration',
        'loan_duration_type',
        'repayment_cycle',
        'number_of_repayments',
        'repayment_mode',
        'releasing_date',
    ];
    
    // Define the foreign key relationships to the Member and LoanProduct models
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function loan_schedules() {
        return $this->hasMany(LoanSchedule::class);
    }
}
