<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:organizations,slug'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'max_borrow_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'late_fine_per_day' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama organisasi wajib diisi.',
            'name.max' => 'Nama organisasi maksimal 255 karakter.',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka, tanda strip, dan garis bawah.',
            'slug.unique' => 'Slug organisasi sudah digunakan, silakan pilih slug lain.',
            'description.required' => 'Deskripsi organisasi wajib diisi.',
            'category.required' => 'Kategori organisasi wajib diisi.',
            'logo.image' => 'Logo harus berupa berkas gambar.',
            'logo.mimes' => 'Format logo harus berupa jpg, jpeg, png, atau webp.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'max_borrow_days.integer' => 'Batas maksimal hari pinjam harus berupa angka bulat.',
            'max_borrow_days.min' => 'Batas pinjam minimal 1 hari.',
            'late_fine_per_day.integer' => 'Denda keterlambatan per hari harus berupa angka bulat.',
            'late_fine_per_day.min' => 'Denda keterlambatan tidak boleh negatif.',
        ];
    }
}
