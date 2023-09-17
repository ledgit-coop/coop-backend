<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'fee',
        'loan_fee_template_id'
    ];

    public function loan_fee_template() {
        return $this->belongsTo(LoanFeeTemplate::class, 'loan_fee_template_id');
    }
}
