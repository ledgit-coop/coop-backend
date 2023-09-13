<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'due_date',
        'principal_amount',
        'interest_amount',
        'fee_amount',
        'penalty_amount',
        'due_amount',
        'principal_balance',
        'is_maturity',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
