<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanFeeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee',
        'fee_type',
        'fee_method',
        'enabled',
        'credit_revenue',
        'credit_share_capital',
        'credit_regular_savings',
        'show_to_report',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'fee' => 'double',
        'credit_revenue'=> 'boolean',
        'credit_share_capital'=> 'boolean',
        'credit_regular_savings'=> 'boolean',
        'show_to_report' => 'boolean',
    ];
}
