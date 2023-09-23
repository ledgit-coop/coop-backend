<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSchedulePenalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_schedule_id',
        'penalty',
        'penalty_date',
        'frequency',
        'method',
    ];

    protected $casts = [
        'penalty_date' => 'datetime:Y-m-d',
    ];

    // Define relationships
    public function loanSchedule()
    {
        return $this->belongsTo(LoanSchedule::class);
    }
}
