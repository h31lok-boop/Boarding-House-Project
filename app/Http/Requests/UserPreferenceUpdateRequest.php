<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserPreferenceUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $arrayFields = [
            'preferred_locations',
            'safety_preferences',
            'amenities',
            'preferred_amenities',
            'preferred_amenity_ids',
            'hobbies',
        ];

        $normalized = collect($arrayFields)
            ->filter(fn (string $field) => is_array($this->input($field)))
            ->mapWithKeys(function (string $field): array {
                $values = collect($this->input($field))
                    ->map(fn (mixed $value) => is_string($value) ? trim($value) : $value)
                    ->filter(fn (mixed $value) => $value !== null && $value !== '')
                    ->unique()
                    ->values()
                    ->all();

                return [$field => $values === [] ? null : $values];
            })
            ->all();

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->isUser();
    }

    public function rules(): array
    {
        $generate = $this->input('intent') === 'generate';

        return [
            'intent' => ['nullable', Rule::in(['save', 'generate'])],
            'family_monthly_income' => ['nullable', 'string', 'max:255'],
            'family_income' => ['nullable', 'string', 'max:255'],
            'monthly_allowance' => ['nullable', 'string', 'max:255'],
            'preferred_rental_budget' => [$generate ? 'required_without:budget_max' : 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'budget_min' => [$generate ? 'required' : 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'budget_max' => [$generate ? 'required_without:preferred_rental_budget' : 'nullable', 'numeric', 'min:0', 'max:999999.99', 'gte:budget_min'],
            'rental_budget' => ['nullable', 'string', 'max:255'],
            'preferred_locations' => [$generate ? 'required' : 'nullable', 'array', 'min:1', 'max:12'],
            'preferred_locations.*' => ['string', 'max:120', 'distinct'],
            'preferred_location' => ['nullable', 'string', 'max:1000'],
            'preferred_landmark' => ['nullable', Rule::in(['DSSC Main Campus', 'Digos City Proper', 'Other'])],
            'distance_from_school' => [$generate ? 'required' : 'nullable', 'numeric', 'min:0.1', 'max:100'],
            'preferred_distance' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'room_type' => [$generate ? 'required' : 'nullable', Rule::in(['any', 'private', 'shared', 'bedspace', 'studio'])],
            'study_habits' => [$generate ? 'required' : 'nullable', Rule::in(['quiet_focus', 'flexible', 'group_study'])],
            'sleeping_schedule' => [$generate ? 'required' : 'nullable', Rule::in(['early_bird', 'balanced', 'night_owl'])],
            'sleep_schedule' => ['nullable', Rule::in(['early_bird', 'balanced', 'night_owl'])],
            'cleanliness_level' => [$generate ? 'required' : 'nullable', 'integer', 'between:1,5'],
            'noise_tolerance' => [$generate ? 'required' : 'nullable', 'integer', 'between:1,100'],
            'safety_preferences' => ['nullable', 'array', 'max:10'],
            'safety_preferences.*' => ['string', 'max:120', 'distinct'],
            'safety_preference' => ['nullable', Rule::in(['standard', 'high', 'very_high'])],
            'amenities' => [$generate ? 'required' : 'nullable', 'array', 'min:1', 'max:20'],
            'amenities.*' => ['string', 'max:120', 'distinct'],
            'preferred_amenities' => ['nullable', 'array', 'max:20'],
            'preferred_amenities.*' => ['string', 'max:120', 'distinct'],
            'preferred_amenity_ids' => ['nullable', 'array', 'max:20'],
            'preferred_amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'lifestyle_notes' => ['nullable', 'string', 'max:1500'],
            'additional_notes' => ['nullable', 'string', 'max:1500'],
            'gender_preference' => ['nullable', Rule::in(['male', 'female', 'mixed', 'no_preference'])],
            'smoking_preference' => ['nullable', Rule::in(['non_smoker_only', 'smoker_ok', 'outdoor_only'])],
            'drinking_preference' => ['nullable', Rule::in(['no_alcohol', 'occasional_ok', 'flexible'])],
            'pets_preference' => ['nullable', Rule::in(['no_pets', 'cat_ok', 'dog_ok', 'pet_friendly'])],
            'internet_usage' => ['nullable', Rule::in(['light', 'moderate', 'heavy', 'remote_work'])],
            'social_style' => ['nullable', Rule::in(['talkative', 'balanced', 'introvert'])],
            'cooking_habit' => ['nullable', Rule::in(['enjoys_cooking', 'occasional_cooking', 'rarely_cooks'])],
            'work_schedule' => ['nullable', Rule::in(['day_schedule', 'flexible_schedule', 'night_shift'])],
            'guest_preference' => ['nullable', Rule::in(['no_guests', 'occasional_guests', 'guests_welcome'])],
            'sharing_style' => ['nullable', Rule::in(['shares_easily', 'ask_first', 'personal_space'])],
            'curfew_preference' => ['nullable', 'string', 'max:60'],
            'hobbies' => ['nullable', 'array', 'max:10'],
            'hobbies.*' => ['string', 'max:60'],
        ];
    }
}
