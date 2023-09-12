<?php

namespace App\Models;

use App\Constants\AddressResidencyStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'oriented',
        'member_number',
        'surname',
        'status',
        'first_name',
        'middle_name',
        'name_extension',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'date_hired',
        'department',
        'position',
        'employee_no',
        'tin_no',
        'email_address',
        'member_at',
        'mobile_number',
        'telephone_number',
        'oriented'
    ];

    protected $appends = [
        'full_name',
        'age',
    ];

    protected $casts = [
        'member_at' => 'datetime',
        'date_of_birth' => 'datetime:Y-m-d'
    ];


    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => (new Carbon($this->date_of_birth))->age,
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => ucwords("$this->first_name $this->middle_name, $this->surname"),
        );
    }

    protected function fullPresentAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $address = $this->member_addresses()->where('type', AddressResidencyStatus::PRESENT)->first();
                return $address ? $address->full_address : '';
            }
        );
    }

    protected function fullPermanentAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $address = $this->member_addresses()->where('type', AddressResidencyStatus::PERMANENT)->first();
                return $address ? $address->full_address : '';
            }
        );
    }

    public function member_addresses() {
        return $this->hasMany(MemberAddress::class);
    }

    public function member_related_people() {
        return $this->hasMany(MemberRelatedPeople::class);
    }

    public function member_accounts() {
        return $this->hasMany(MemberAccount::class);
    }
}
