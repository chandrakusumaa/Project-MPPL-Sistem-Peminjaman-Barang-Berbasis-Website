<?php

namespace App\Http\Requests\Membership;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', 'string', Rule::in([Role::STAFF->value, Role::MEMBER->value])],
        ];
    }
}
