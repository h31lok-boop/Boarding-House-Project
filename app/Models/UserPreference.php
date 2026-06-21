<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    public const AI_REQUIRED_FIELDS = [
        'preferred_rental_budget',
        'preferred_locations',
        'distance_from_school',
        'room_type',
        'study_habits',
        'sleeping_schedule',
        'cleanliness_level',
        'amenities',
    ];

    protected $fillable = [
        'user_id',
        'family_monthly_income',
        'monthly_allowance',
        'preferred_rental_budget',
        'preferred_rental_budget_min',
        'preferred_rental_budget_max',
        'preferred_locations',
        'preferred_landmark',
        'distance_from_school',
        'room_type',
        'study_habits',
        'sleeping_schedule',
        'cleanliness_level',
        'noise_tolerance',
        'safety_preferences',
        'amenities',
        'lifestyle_notes',
        'profile_completion',
    ];

    protected $casts = [
        'preferred_rental_budget' => 'decimal:2',
        'preferred_rental_budget_min' => 'decimal:2',
        'preferred_rental_budget_max' => 'decimal:2',
        'preferred_locations' => 'array',
        'distance_from_school' => 'decimal:2',
        'cleanliness_level' => 'integer',
        'noise_tolerance' => 'integer',
        'safety_preferences' => 'array',
        'amenities' => 'array',
        'profile_completion' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isAiReady(): bool
    {
        return $this->missingAiFields() === [];
    }

    public function missingAiFields(): array
    {
        return collect(self::AI_REQUIRED_FIELDS)
            ->reject(fn (string $field) => $this->fieldIsFilled($this->{$field}))
            ->values()
            ->all();
    }

    public function aiCompletionPercentage(): int
    {
        $filled = count(self::AI_REQUIRED_FIELDS) - count($this->missingAiFields());

        return (int) round(($filled / count(self::AI_REQUIRED_FIELDS)) * 100);
    }

    public function completionSections(?User $user = null): array
    {
        $user ??= $this->user;
        $phone = $user?->phone ?: $user?->phone_number ?: $user?->contact_number;

        $sections = [
            'Basic Information' => [
                $user?->name,
                $this->family_monthly_income,
                $this->monthly_allowance,
                $this->preferred_rental_budget,
            ],
            'Contact Details' => [
                $user?->email,
                $phone,
            ],
            'Lifestyle Preferences' => collect(self::AI_REQUIRED_FIELDS)
                ->map(fn (string $field) => $this->{$field})
                ->all(),
            'Privacy Settings' => [
                $user?->show_profile_to_owners,
                $user?->allow_owner_messages,
                $user?->allow_matchmaking_data,
            ],
        ];

        return collect($sections)->map(function (array $values): array {
            $filled = collect($values)->filter(fn (mixed $value) => $this->fieldIsFilled($value))->count();
            $total = count($values);

            return [
                'complete' => $total > 0 && $filled === $total,
                'filled' => $filled,
                'total' => $total,
            ];
        })->all();
    }

    public function calculateProfileCompletion(?User $user = null): int
    {
        $sections = collect($this->completionSections($user));
        $filled = $sections->sum('filled');
        $total = $sections->sum('total');

        return $total > 0 ? (int) round(($filled / $total) * 100) : 0;
    }

    private function fieldIsFilled(mixed $value): bool
    {
        if (is_array($value)) {
            return collect($value)->filter(fn (mixed $item) => $item !== null && $item !== '')->isNotEmpty();
        }

        return $value !== null && $value !== '';
    }
}
