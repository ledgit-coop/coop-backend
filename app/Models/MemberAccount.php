<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'account_holder',
        'member_id',
        'account_id',
        'passbook_count',
        'balance',
        'interest_per_anum',

        'earn_interest_per_anum',
        'maintaining_balance',
        'penalty_below_maintaining_method',
        'penalty_below_maintaining',
    ];

    public function account() {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions() {
        return $this->hasMany(AccountTransaction::class);
    }

    public function latest_transaction() {
        return $this->hasOne(AccountTransaction::class)->orderBy('transaction_date', 'desc');
    }
}
