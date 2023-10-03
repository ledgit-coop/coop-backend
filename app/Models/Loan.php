<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'loan_number',
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
        'email',
        'approved_date',
        'applied_date',
        'guarantor_first_id',
        'guarantor_second_id',
        'released_date',
        'released',
        'released_amount',
        'interest_amount',
        'due_amount',
        'penalty',
        'penalty_duration',
        'penalty_grace_period',
        'penalty_method',

        'pre_termination_fee',
        'pre_termination_panalty',
        'pre_termination_panalty_method',
        
        'next_payroll_date'
    ];

    protected $casts = [
        'pre_termination_fee' => 'integer',
        'pre_termination_panalty' => 'integer',
        'applied_amount' => 'integer',
        'principal_amount' => 'integer',
        'loan_duration' => 'integer',
        'penalty' => 'integer',
        'number_of_repayments' => 'integer',
        'loan_interest' => 'integer',
        'released_amount' => 'integer',
        'interest_amount' => 'integer',
        'due_amount' => 'integer',
        'released_date' => 'datetime:Y-m-d',
        'next_payroll_date' => 'datetime:Y-m-d',
        'released' => 'boolean',
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

    public function member_account() {
        return $this->belongsTo(MemberAccount::class, 'member_account_id');
    }

    public function guarantor_first() {
        return $this->belongsTo(Member::class, 'guarantor_first_id');
    }

    public function guarantor_second() {
        return $this->belongsTo(Member::class, 'guarantor_second_id');
    }

    protected function overdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->loan_schedules()->where('overdue', 1)->exists(),
        );
    }

    protected function outstanding(): Attribute
    {
        return Attribute::make(
            get: function() {
                $schedules = $this->loan_schedules;
                return $this->due_amount - $schedules->sum('amount_paid');
            }
        );
    }

    protected function widgetData(): Attribute
    {
        return Attribute::make(
            get: function() {
                $schedules = $this->loan_schedules;
                $paid = $schedules->where('paid', true)->count();
                $unpaid = $schedules->where('paid', false)->count();
                
                return [
                    'interest' => "$this->loan_interest/$this->loan_interest_period",
                    'loan_duration' => "$this->loan_duration/$this->loan_duration_type",
                    'paid_count' => $paid,
                    'unpaid_count' => $unpaid,
                    'overdue' => $this->overdue,
                ];
            }
        );
    }

    public function loan_fees() {
        return $this->hasMany(LoanFee::class);
    }

    public function loan_product() {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }
}
