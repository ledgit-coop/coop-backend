<?php

namespace App\Models;

use App\Constants\AccountTransactionType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'member_account_id',
        'particular',
        'amount',
        'transaction_date',
        'remaining_balance',
        'posted',
        'type',
    ];

    protected $appends = [
        'transaction_type',
        'account_name',
    ];

    protected $casts = [
        'posted' => 'boolean',
        'amount' => 'double',
    ];


    public function member_account() {
        return $this->belongsTo(MemberAccount::class, 'member_account_id');
    }

    protected function transactionType(): Attribute
    {
        return Attribute::make(
            get: function() {
                return $this->amount < 0 ? AccountTransactionType::WITHDRAWAL : AccountTransactionType::DEPOSIT;
            }
        );
    }

    protected function accountName(): Attribute
    {
        return Attribute::make(
            get: function() {
                return $this->member_account->account->name;
            }
        );
    }
}
