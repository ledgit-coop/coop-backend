<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRelatedPeople extends Model
{
    use HasFactory;
    
    protected $table = 'member_related_people';

    protected $fillable = [
        'member_id',
        'surname',
        'first_name',
        'middle_name',
        'name_extension',
        'date_of_birth',
        'occupation',
        'contact_number',
        'type',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => ucwords("$this->first_name $this->middle_name $this->surname"),
        );
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

}
