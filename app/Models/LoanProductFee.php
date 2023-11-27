<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProductFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_fee_template_id',
        'fee'
    ];

    protected $casts = [
        'fee' => 'integer',
        'loan_fee_template_id' => 'integer',
    ];

    public function loan_fee_template() {
        return $this->belongsTo(LoanFeeTemplate::class, 'loan_fee_template_id');
    }
}
