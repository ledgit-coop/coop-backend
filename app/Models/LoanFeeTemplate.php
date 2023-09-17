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
        'enabled'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'fee' => 'integer'
    ];
}
