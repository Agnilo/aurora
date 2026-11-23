<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'handle' => [
                'nullable',
                'string',
                'max:40',
                'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique('user_details', 'handle')->ignore($this->user()->id, 'user_id'),
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'handle.regex' => t('profile.handle_format'),
            'handle.unique' => t('profile.handle_taken'),
            'gender.in' => t('profile.invalid_gender'),
            'birthdate.before' => t('profile.birthdate_before_today'),
        ];
    }
}
