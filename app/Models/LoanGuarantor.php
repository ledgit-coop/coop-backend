<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LoanGuarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'contact',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => ucwords("$this->first_name $this->last_name"),
        );
    }



}
