<?php

namespace App\Http\Requests\Membership;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via Livewire
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in([Role::ADMIN->value, Role::STAFF->value, Role::MEMBER->value])],
        ];
    }
}
