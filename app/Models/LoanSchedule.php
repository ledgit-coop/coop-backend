<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LoanSchedule extends Model
{
    use HasFactory;

    const ALMOST_DAY_THRESHOLD = 4;

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
        'amount_paid',
        'transaction_id',
        'overdue',
        'payment_reference',
        'payment_remarks',
        'paid',
    ];

    protected $casts = [
        'due_date' => 'datetime:Y-m-d',
    ];

    protected $appends = [
        'due_humans',
        'almost_due'
    ];

    protected function dueHumans(): Attribute
    {
        return Attribute::make(
            get: function() {

                if($this->paid) {
                    return "Paid";
                }
               
                $days = $this->due_days;

                if($days == 0)
                    $message = 'Due Today';
                else if($days < 0)
                    $message = abs($days) . " day(s) overdue";
                else if($days <= self::ALMOST_DAY_THRESHOLD && $days > 0)
                    $message = "Due in ". abs($days) . " day(s)";
                else if($days > self::ALMOST_DAY_THRESHOLD)
                    $message = "Incoming";

                return $message;
            }
        );
    }

    protected function almostDue(): Attribute
    {
        return Attribute::make(
            get: function() {
                return $this->due_days <= self::ALMOST_DAY_THRESHOLD && $this->due_days >= 0;
            }
        );
    }

    protected function overdue(): Attribute
    {
        return Attribute::make(
            get: function() {
                return $this->due_days < 0;
            }
        );
    }

    protected function dueDays(): Attribute
    {
        return Attribute::make(
            get: function() {
                return round(Carbon::now()->floatDiffInDays($this->due_date, false));
            }
        );
    }
    
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function transaction() {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
