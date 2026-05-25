<?php

namespace App\Services;

use App\Models\TenantMatchProfile;
use App\Models\User;

class CompatibilityService
{
    public function score(User $tenant, User $candidate): array
    {
        $tenantProfile = $tenant->tenantMatchProfile;
        $candidateProfile = $candidate->tenantMatchProfile;

        if (! $tenantProfile || ! $candidateProfile) {
            return [
                'overall_score' => 0.0,
                'compatibility_percent' => 0,
                'breakdown' => [],
                'highlights' => [],
                'conflicts' => ['Incomplete match profile'],
            ];
        }

        $weights = config('matchmaking.weights', []);
        $breakdown = [];
        $total = 0.0;
        $weightTotal = 0.0;

        foreach ($weights as $criterion => $weight) {
            $score = $this->scoreCriterion($criterion, $tenantProfile, $candidateProfile);
            $weighted = $score * (float) $weight;
            $weightTotal += (float) $weight;
            $total += $weighted;

            $breakdown[$criterion] = [
                'score' => round($score, 4),
                'weighted_score' => round($weighted, 4),
                'weight' => (float) $weight,
                'label' => $this->criterionLabel($criterion),
            ];
        }

        $overall = $weightTotal > 0 ? $total / $weightTotal : 0.0;
        [$highlights, $conflicts] = $this->summarizeCompatibility($tenantProfile, $candidateProfile, $breakdown);

        return [
            'overall_score' => round($overall, 4),
            'compatibility_percent' => (int) round($overall * 100),
            'breakdown' => $breakdown,
            'highlights' => $highlights,
            'conflicts' => $conflicts,
        ];
    }

    private function scoreCriterion(string $criterion, TenantMatchProfile $tenant, TenantMatchProfile $candidate): float
    {
        return match ($criterion) {
            'budget' => $this->scoreBudget($tenant, $candidate),
            'gender_preference' => $this->scoreGenderPreference($tenant, $candidate),
            'sleep_schedule' => $this->scoreEquality($tenant->sleep_schedule, $candidate->sleep_schedule),
            'study_habits' => $this->scoreEquality($tenant->study_habits, $candidate->study_habits),
            'cleanliness_level' => $this->scoreScaleDistance($tenant->cleanliness_level, $candidate->cleanliness_level, 5),
            'noise_tolerance' => $this->scoreScaleDistance($tenant->noise_tolerance, $candidate->noise_tolerance, 5),
            'smoking_preference' => $this->scoreEquality($tenant->smoking_preference, $candidate->smoking_preference),
            'drinking_preference' => $this->scoreEquality($tenant->drinking_preference, $candidate->drinking_preference),
            'pets_preference' => $this->scoreEquality($tenant->pets_preference, $candidate->pets_preference),
            'internet_usage' => $this->scoreEquality($tenant->internet_usage, $candidate->internet_usage),
            'hobbies' => $this->scoreHobbies($tenant->hobbies ?? [], $candidate->hobbies ?? []),
            default => 0.0,
        };
    }

    private function scoreBudget(TenantMatchProfile $tenant, TenantMatchProfile $candidate): float
    {
        $tenantMin = $this->toFloat($tenant->budget_min);
        $tenantMax = $this->toFloat($tenant->budget_max);
        $candidateMin = $this->toFloat($candidate->budget_min);
        $candidateMax = $this->toFloat($candidate->budget_max);

        if ($tenantMin === null || $tenantMax === null || $candidateMin === null || $candidateMax === null) {
            return 0.5;
        }

        $overlapMin = max($tenantMin, $candidateMin);
        $overlapMax = min($tenantMax, $candidateMax);

        if ($overlapMax < $overlapMin) {
            return 0.0;
        }

        $unionMin = min($tenantMin, $candidateMin);
        $unionMax = max($tenantMax, $candidateMax);
        $unionWidth = max($unionMax - $unionMin, 1.0);
        $overlapWidth = max($overlapMax - $overlapMin, 0.0);

        return min(max($overlapWidth / $unionWidth, 0.0), 1.0);
    }

    private function scoreGenderPreference(TenantMatchProfile $tenant, TenantMatchProfile $candidate): float
    {
        $tenantPreference = (string) ($tenant->gender_preference ?? 'no_preference');
        $candidatePreference = (string) ($candidate->gender_preference ?? 'no_preference');

        if ($tenantPreference === 'no_preference' || $candidatePreference === 'no_preference') {
            return 1.0;
        }

        return $tenantPreference === $candidatePreference ? 1.0 : 0.0;
    }

    private function scoreEquality(?string $tenantValue, ?string $candidateValue): float
    {
        if (! $tenantValue || ! $candidateValue) {
            return 0.5;
        }

        return $tenantValue === $candidateValue ? 1.0 : 0.0;
    }

    private function scoreScaleDistance(mixed $tenantValue, mixed $candidateValue, int $maxScale): float
    {
        if (! is_numeric($tenantValue) || ! is_numeric($candidateValue)) {
            return 0.5;
        }

        $distance = abs((int) $tenantValue - (int) $candidateValue);
        $maxDistance = max($maxScale - 1, 1);

        return max(0.0, 1.0 - ($distance / $maxDistance));
    }

    private function scoreHobbies(array $tenantHobbies, array $candidateHobbies): float
    {
        $tenantSet = array_values(array_unique(array_filter($tenantHobbies)));
        $candidateSet = array_values(array_unique(array_filter($candidateHobbies)));

        if ($tenantSet === [] || $candidateSet === []) {
            return 0.5;
        }

        $intersection = array_intersect($tenantSet, $candidateSet);
        $union = array_unique(array_merge($tenantSet, $candidateSet));

        return count($union) > 0 ? count($intersection) / count($union) : 0.0;
    }

    private function summarizeCompatibility(TenantMatchProfile $tenant, TenantMatchProfile $candidate, array $breakdown): array
    {
        $highlights = [];
        $conflicts = [];

        foreach ($breakdown as $criterion => $item) {
            $score = (float) $item['score'];

            if ($score >= 0.9) {
                $highlights[] = $this->criterionSummary($criterion, $tenant, $candidate);
            } elseif ($score <= 0.2) {
                $conflicts[] = $this->criterionSummary($criterion, $tenant, $candidate);
            }
        }

        return [
            array_values(array_unique(array_filter($highlights))),
            array_values(array_unique(array_filter($conflicts))),
        ];
    }

    private function criterionSummary(string $criterion, TenantMatchProfile $tenant, TenantMatchProfile $candidate): ?string
    {
        return match ($criterion) {
            'budget' => 'Budget expectations align',
            'gender_preference' => 'Gender preference is compatible',
            'sleep_schedule' => $tenant->sleep_schedule === $candidate->sleep_schedule
                ? 'Similar sleeping schedule'
                : 'Different sleeping schedule',
            'study_habits' => $tenant->study_habits === $candidate->study_habits
                ? 'Compatible study habits'
                : 'Different study habits',
            'cleanliness_level' => 'Cleanliness expectations are close',
            'noise_tolerance' => 'Noise tolerance is close',
            'smoking_preference' => $tenant->smoking_preference === $candidate->smoking_preference
                ? 'Smoking preference matches'
                : 'Smoking preference differs',
            'drinking_preference' => $tenant->drinking_preference === $candidate->drinking_preference
                ? 'Drinking preference matches'
                : 'Drinking preference differs',
            'pets_preference' => $tenant->pets_preference === $candidate->pets_preference
                ? 'Pets preference matches'
                : 'Pets preference differs',
            'internet_usage' => $tenant->internet_usage === $candidate->internet_usage
                ? 'Internet usage patterns align'
                : 'Internet usage differs',
            'hobbies' => 'Shared hobbies or interests',
            default => null,
        };
    }

    private function criterionLabel(string $criterion): string
    {
        return match ($criterion) {
            'budget' => 'Budget',
            'gender_preference' => 'Gender Preference',
            'sleep_schedule' => 'Sleeping Schedule',
            'study_habits' => 'Study Habits',
            'cleanliness_level' => 'Cleanliness',
            'noise_tolerance' => 'Noise Tolerance',
            'smoking_preference' => 'Smoking Preference',
            'drinking_preference' => 'Drinking Preference',
            'pets_preference' => 'Pets Preference',
            'internet_usage' => 'Internet Usage',
            'hobbies' => 'Hobbies',
            default => ucfirst(str_replace('_', ' ', $criterion)),
        };
    }

    private function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
