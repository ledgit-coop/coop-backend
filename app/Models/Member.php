<?php

namespace App\Models;

use App\Constants\AccountType;
use App\Constants\AddressResidencyStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Member extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'oriented',
        'profile_picture_url'
    ];

    protected $appends = [
        'full_name',
        'age',
    ];

    protected $casts = [
        'member_at' => 'datetime:Y-m-d',
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
            get: fn () => ucwords("$this->first_name $this->middle_name $this->surname"),
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

    protected function residencyStatus(): Attribute
    {
        return Attribute::make(
            get: function() {
                $address = $this->member_addresses()->where('type', AddressResidencyStatus::PRESENT)->first();
                return $address ? $address->residency_status : '';
            }
        );
    }

    public function present_address()
    {
        return $this->hasOne(MemberAddress::class, 'member_id')->where('type', AddressResidencyStatus::PRESENT);
    }

    public function permanent_address()
    {
        return $this->hasOne(MemberAddress::class)->where('type', AddressResidencyStatus::PERMANENT);
    }

    public function beneficiaries() {
        return $this->hasMany(MemberBeneficiary::class);
    }

    public function member_addresses() {
        return $this->hasMany(MemberAddress::class);
    }

    public function member_related_people() {
        return $this->hasMany(MemberRelatedPeople::class);
    }

    public function mother() {
        return $this->hasOne(MemberRelatedPeople::class)->where('type', 'mother');
    }

    public function spouse() {
        return $this->hasOne(MemberRelatedPeople::class)->where('type', 'spouse');
    }

    public function father() {
        return $this->hasOne(MemberRelatedPeople::class)->where('type', 'father');
    }

    public function member_accounts() {
        return $this->hasMany(MemberAccount::class);
    }

    public function share_capital_account() {
        return $this->hasOne(MemberAccount::class)->whereHas('account', function($account) {
            $account->where('type', AccountType::SHARE_CAPITAL);
        });
    }

    public function savings_accounts() {
        return $this->hasMany(MemberAccount::class)->whereHas('account', function($account) {
            $account->where('type', AccountType::SAVINGS);
        });
    }
}
