<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantMatchProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->isUser();
    }

    public function rules(): array
    {
        $stringOptions = [
            'gender_preference' => ['male', 'female', 'mixed', 'no_preference'],
            'sleep_schedule' => ['early_bird', 'balanced', 'night_owl'],
            'study_habits' => ['quiet_focus', 'flexible', 'group_study'],
            'smoking_preference' => ['non_smoker_only', 'smoker_ok', 'outdoor_only'],
            'drinking_preference' => ['no_alcohol', 'occasional_ok', 'flexible'],
            'pets_preference' => ['no_pets', 'cat_ok', 'dog_ok', 'pet_friendly'],
            'internet_usage' => ['light', 'moderate', 'heavy', 'remote_work'],
        ];

        $hobbyOptions = [
            'reading',
            'gaming',
            'sports',
            'music',
            'cooking',
            'fitness',
            'movies',
            'travel',
            'art',
            'coding',
        ];

        return [
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'max:999999.99', 'gte:budget_min'],
            'family_income' => ['nullable', 'string', 'max:255'],
            'monthly_allowance' => ['nullable', 'string', 'max:255'],
            'rental_budget' => ['nullable', 'string', 'max:255'],
            'preferred_location' => ['nullable', 'string', 'max:1000'],
            'preferred_distance' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'room_type' => ['nullable', 'string', 'max:60'],
            'safety_preference' => ['nullable', 'string', 'max:60'],
            'curfew_preference' => ['nullable', 'string', 'max:60'],
            'gender_preference' => ['required', Rule::in($stringOptions['gender_preference'])],
            'sleep_schedule' => ['required', Rule::in($stringOptions['sleep_schedule'])],
            'study_habits' => ['required', Rule::in($stringOptions['study_habits'])],
            'cleanliness_level' => ['required', 'integer', 'between:1,5'],
            'noise_tolerance' => ['required', 'integer', 'between:0,100'],
            'smoking_preference' => ['required', Rule::in($stringOptions['smoking_preference'])],
            'drinking_preference' => ['required', Rule::in($stringOptions['drinking_preference'])],
            'pets_preference' => ['required', Rule::in($stringOptions['pets_preference'])],
            'internet_usage' => ['required', Rule::in($stringOptions['internet_usage'])],
            'hobbies' => ['nullable', 'array', 'max:6'],
            'hobbies.*' => ['string', Rule::in($hobbyOptions)],
            'preferred_amenity_ids' => ['nullable', 'array', 'max:10'],
            'preferred_amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'preferred_amenities' => ['nullable', 'array', 'max:20'],
            'preferred_amenities.*' => ['string', 'max:120'],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
