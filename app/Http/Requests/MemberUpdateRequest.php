<?php

namespace App\Http\Requests;

class MemberUpdateRequest extends MemberRequest
{
    public function rules()
    {
        $member = $this->route('member');
        $rules = parent::rules();

        return [
            ...$rules,
            'member_number' => 'required|unique:members,member_number,' . $member->id,
        ];
    }

    public function messages()
    {
        return [
            'member_number' => [
                'unique' => 'Id number has already been taken.'
            ]
        ];
    }
}
