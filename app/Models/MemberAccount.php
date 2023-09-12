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
    ];

    public function account() {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions() {
        return $this->hasMany(AccountTransaction::class);
    }
}
