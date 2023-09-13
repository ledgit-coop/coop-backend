<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'member_id',
        'account_id',
        'passbook_count',
        'balance',
        'interest_per_anum',
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
