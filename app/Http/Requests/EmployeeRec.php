<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRec extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
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
            'name.required' => 'Nama karyawan wajib diisi.',
            'name.regex'    => 'Nama hanya boleh mengandung huruf, angka, spasi, strip, dan titik.',
            'position.nullable' => 'Jabatan bersifat opsional.',
            'email.email'   => 'Format email tidak valid.',
        ];
    }
}