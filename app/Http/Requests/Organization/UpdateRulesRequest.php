<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'max_borrow_days' => ['required', 'integer', 'min:1'],
            'late_fine_per_day' => ['required', 'integer', 'min:0'],
        ];
    }
}
