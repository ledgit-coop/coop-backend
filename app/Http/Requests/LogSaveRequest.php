<?php

namespace App\Http\Requests;

use App\Constants\LogModules;
use App\Constants\LogTypes;
use Illuminate\Foundation\Http\FormRequest;

class LogSaveRequest extends FormRequest
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
            'module' => ['required', 'string', 'in:' . implode(',', LogModules::MODULES)],
            'module_id' => 'required',
            'type' => ['required', 'string', 'in:' . implode(',', LogTypes::TYPES)],
            'content' => 'required',

            'parent_module' => ['nullable', 'string', 'in:' . implode(',', LogModules::MODULES)],
            'parent_module_id' => 'nullable',
        ];
    }
}
