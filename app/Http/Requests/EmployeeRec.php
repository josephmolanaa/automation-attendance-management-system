<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRec extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
{
    $employeeId = $this->route('employee') ? $this->route('employee')->id : null;

    return [
        'emp_id'   => 'nullable',
        'name'     => ['required', 'regex:/^[a-zA-Z0-9\s\-\_\.]+$/u'],
        'position' => 'nullable',
        'email'    => 'nullable|email',
        'pin_code' => 'nullable',
        'schedule' => 'nullable',
    ];
}

    public function messages()
    {
        return [
            'emp_id.nullable' => 'Employee ID is optional.',
            'name.required' => 'Employee name is required.',
            'name.regex'    => 'Employee name can only contain letters, numbers, spaces, hyphens, and periods.',
            'position.nullable' => 'Position is optional.',
            'email.email'   => 'Invalid email format.',
        ];
    }
}