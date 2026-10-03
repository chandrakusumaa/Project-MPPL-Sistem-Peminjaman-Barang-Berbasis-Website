<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Skema JSON preferensi: semua key kategori wajib ada dan bertipe boolean.
     * Kategori `damage` bersifat transaksional sehingga harus selalu true.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (User::NOTIFICATION_CATEGORIES as $category) {
            $rules[$category] = ['required', 'boolean'];
        }

        foreach (User::MANDATORY_EMAIL_CATEGORIES as $category) {
            $rules[$category] = ['required', 'accepted'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Preferensi notifikasi :attribute wajib diisi.',
            'boolean' => 'Preferensi notifikasi :attribute tidak valid.',
            'damage.accepted' => 'Email laporan kerusakan bersifat wajib dan tidak dapat dimatikan.',
        ];
    }

    /**
     * Validasi array preferensi memakai aturan yang sama (dipakai ulang oleh Livewire).
     *
     * @param  array<string, mixed>  $preferences
     * @return array<string, bool>
     */
    public static function validatePreferences(array $preferences): array
    {
        $request = new static;

        Validator::make($preferences, $request->rules(), $request->messages())->validate();

        return User::normalizeNotificationPreferences($preferences);
    }
}
