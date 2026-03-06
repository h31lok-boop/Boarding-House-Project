<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantAccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->isTenant();
    }

    /**
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
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'profile_image_remove' => ['nullable', 'boolean'],
            'notify_payment_reminders' => ['nullable', 'boolean'],
            'notify_booking_updates' => ['nullable', 'boolean'],
            'notify_ticket_updates' => ['nullable', 'boolean'],
        ];
    }
}
