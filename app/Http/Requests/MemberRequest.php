<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'profile_picture_url' => 'nullable|string',
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'name_extension' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|date_format:Y-m-d',
            'place_of_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female', // Assuming 'Male' and 'Female' are the allowed values
            'date_hired' => 'nullable|date|date_format:Y-m-d',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'tin_no' => 'nullable|string|max:255',
            'email_address' => 'nullable|email|max:255',
            'member_at' => 'required|date',
            'mobile_number' => 'nullable|string|max:255',
            'telephone_number' => 'nullable|string|max:255',
            'oriented' => 'nullable|boolean',

            'permanent_address.house_block_lot'=> 'nullable|string|max:255',
            'permanent_address.street'=> 'nullable|string|max:255',
            'permanent_address.subdivision_village'=> 'nullable|string|max:255',
            'permanent_address.barangay'=> 'nullable|string|max:255',
            'permanent_address.city_municipality'=> 'nullable|string|max:255',
            'permanent_address.province'=> 'nullable|string|max:255',
            'permanent_address.zip_code'=> 'nullable|integer',

            'present_address.house_block_lot'=> 'nullable|string|max:255',
            'present_address.street'=> 'nullable|string|max:255',
            'present_address.subdivision_village'=> 'nullable|string|max:255',
            'present_address.barangay'=> 'nullable|string|max:255',
            'present_address.city_municipality'=> 'nullable|string|max:255',
            'present_address.province'=> 'nullable|string|max:255',
            'present_address.zip_code'=> 'nullable|integer',
            'present_address.residency_status'=> 'nullable|string|max:255',

         
            'father.surname'=> 'nullable|string|max:255',
            'father.first_name'=> 'nullable|string|max:255',
            'father.middle_name'=> 'nullable|string|max:255',
            'father.name_extension'=> 'nullable|string|max:255',
            'father.date_of_birth'=> 'nullable|date|date_format:Y-m-d',
            'father.occupation'=> 'nullable|string|max:255',
            'father.contact_number'=> 'nullable|string|max:255',
           

            'mother.surname'=> 'nullable|string|max:255',
            'mother.first_name'=> 'nullable|string|max:255',
            'mother.middle_name'=> 'nullable|string|max:255',
            'mother.name_extension'=> 'nullable|string|max:255',
            'mother.date_of_birth'=> 'nullable|date|date_format:Y-m-d',
            'mother.occupation'=> 'nullable|string|max:255',
            'mother.contact_number'=> 'nullable|string|max:255',

            'spouse.surname'=> 'nullable|string|max:255',
            'spouse.first_name'=> 'nullable|string|max:255',
            'spouse.middle_name'=> 'nullable|string|max:255',
            'spouse.name_extension'=> 'nullable|string|max:255',
            'spouse.date_of_birth'=> 'nullable|date|date_format:Y-m-d',
            'spouse.occupation'=> 'nullable|string|max:255',
            'spouse.contact_number'=> 'nullable|string|max:255',
            
            'beneficiaries.*.name'=> 'nullable|string|max:255',
            'beneficiaries.*.birthdate'=> 'nullable|date|date_format:Y-m-d',
            'beneficiaries.*.relationship'=> 'nullable|string|max:255',
        ];
    }
}
