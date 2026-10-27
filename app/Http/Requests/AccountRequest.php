<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->email))]);
    }

    public function rules(): array
    {
        return $this->routeIs('register.store') ? ['name' => 'required|string|max:150', 'email' => 'required|email|max:254|unique:users,email', 'password' => ['required', 'confirmed', Password::min(8)]] : ['email' => 'required|email', 'password' => 'required|string'];
    }
}
