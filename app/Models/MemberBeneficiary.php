<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberBeneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'birthdate',
        'relationship',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
