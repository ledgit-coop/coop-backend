<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualReturn extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'to_date',
        'from_date',
        'interest_income_on_loan',
        'service_fees',
        'membership_fees',
        'gross_surplus',
        'operating_expenses',
        'net_suprplus_allocation_distribution',
        'reserve_fund_percent',
        'reserve_fund',
        'educational_training_fund_percent',
        'educational_training_fund',
        'educational_training_fund_due_cetf',
        'educational_training_fund_due_etf',
        'optional_fund_percent',
        'optional_fund',
        'interest_on_share_capital',
        'patronage_refund',
        'net_surplus_allocated_distributed',
        'interest_on_share_capital_rate_interest',
        'patronage_refund_rate_interest',
        'created_by',

        'interest_on_share_capital_allocation_percent',
        'patronage_refund_allocation_percent',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    protected $casts = [
        'to_date' => 'date',
        'from_date' => 'date',
        'interest_income_on_loan' => 'double',
        'service_fees' => 'double',
        'membership_fees' => 'double',
        'gross_surplus' => 'double',
        'operating_expenses' => 'double',
        'net_suprplus_allocation_distribution' => 'double',
        'reserve_fund_percent' => 'double',
        'reserve_fund' => 'double',
        'educational_training_fund_percent' => 'double',
        'educational_training_fund' => 'double',
        'educational_training_fund_due_cetf' => 'double',
        'educational_training_fund_due_etf' => 'double',
        'optional_fund_percent' => 'double',
        'optional_fund' => 'double',
        'interest_on_share_capital' => 'double',
        'patronage_refund' => 'double',
        'net_surplus_allocated_distributed' => 'double',
        'interest_on_share_capital_rate_interest' => 'double',
        'patronage_refund_rate_interest' => 'double',

        'interest_on_share_capital_allocation_percent' => 'double',
        'patronage_refund_allocation_percent' => 'double',
    ];
}
