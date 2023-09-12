<?php

namespace App\Models;

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

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

}
