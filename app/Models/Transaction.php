<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'amount',
        'type',
        'transaction_date',
        'particular',
        'parameters',
        'posted',
        'created_by',
        'transaction_sub_type_id',
    ];

    protected $casts = [
        'amount' => 'double',
        'posted' => 'bool'
    ];
}
