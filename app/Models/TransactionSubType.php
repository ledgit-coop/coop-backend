<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionSubType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'type',
        'locked',
    ];

    protected $casts = ['locked'];

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
