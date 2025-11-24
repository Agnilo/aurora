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
                'handle.regex' => 'Handle gali būti tik raidės, skaičiai ir _ be tarpų.',
                'handle.unique' => 'Toks handle jau egzistuoja.',
                'gender.in' => 'Lyties pasirinkimas neteisingas.',
                'birthdate.before' => 'Gimimo data turi būti ankstesnė nei šiandiena.',
            ];
        }
}
