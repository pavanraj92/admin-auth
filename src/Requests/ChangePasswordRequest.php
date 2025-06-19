<?php

namespace admin\admin_auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [                   
            'old_password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'different:old_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/'
            ],
            'confirm_new_password' => [
                'required',
                'same:new_password'
            ],
        ];
    }

    
    public function messages()
    {
        return [
            'new_password.regex' => 'Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!Hash::check($this->old_password, auth()->user()->password)) {
                $validator->errors()->add('old_password', 'The old password is incorrect.');
            }
        });
    }
}
