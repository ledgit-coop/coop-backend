<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'house_block_lot',
        'street',
        'subdivision_village',
        'barangay',
        'city_municipality',
        'province',
        'zip_code',
        'residency_status',
        'type',
    ];

    protected $casts = [
        'zip_code' => 'integer'
    ];

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $components = [
                    $this->house_block_lot,
                    $this->street,
                    $this->subdivision_village,
                    $this->barangay,
                    $this->city_municipality,
                    $this->province,
                    $this->zip_code,
                ];
            
                // Filter out empty address components
                $filteredComponents = array_filter($components, function($component) {
                    return !empty($component);
                });
            
                // Concatenate the non-empty address components
                $fullAddress = implode(', ', $filteredComponents);
            
                return preg_replace('/^\s+|\s+$/m', '', rtrim($fullAddress, ','));
            },
        );
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
