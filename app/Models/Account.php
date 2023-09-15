<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'type',
        'earn_interest_per_anum',
        'maintaining_balance',
        'penalty_below_maintaining_method',
        'penalty_below_maintaining',
        'penalty_below_maintaining_cycle',
        'penalty_below_maintaining_duration',
    ];
}
