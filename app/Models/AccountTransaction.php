<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'member_account_id',
        'particular',
        'amount',
    ];

    public function member_account() {
        return $this->belongsTo(MemberAccount::class, 'member_account_id');
    }
}
