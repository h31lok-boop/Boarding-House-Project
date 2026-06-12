<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BoardMatchStrongPassword implements ValidationRule
{
    private const MESSAGE = 'This password is too predictable. Please create a stronger password.';

    /**
     * @var array<int, string>
     */
    private array $predictablePasswords = [
        '123456',
        '12345678',
        '123456789',
        'abcdef',
        'abcdef123',
        'qwerty',
        'qwerty123',
        'password',
        'password123',
        'admin123',
        'user123',
        '111111',
        '000000',
        'boardmatch123',
        'abc123',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;
        $normalized = strtolower(preg_replace('/\s+/', '', $password) ?? '');

        if ($normalized === '') {
            return;
        }

        if ($this->isPredictable($normalized) || $this->hasRepeatedCharacters($normalized) || $this->hasSequentialRun($normalized)) {
            $fail(self::MESSAGE);
        }
    }

    private function isPredictable(string $normalized): bool
    {
        if (in_array($normalized, $this->predictablePasswords, true)) {
            return true;
        }

        foreach (['password', 'qwerty', 'boardmatch', 'admin123', 'user123'] as $token) {
            if (str_contains($normalized, $token)) {
                return true;
            }
        }

        return false;
    }

    private function hasRepeatedCharacters(string $normalized): bool
    {
        return (bool) preg_match('/(.)\1{5,}/', $normalized);
    }

    private function hasSequentialRun(string $normalized): bool
    {
        $sequences = [
            '0123456789',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'qwertyuiopasdfghjklzxcvbnm',
            'mnbvcxzlkjhgfdsa poiuytrewq',
        ];

        foreach ($sequences as $sequence) {
            $sequence = str_replace(' ', '', $sequence);

            for ($length = 6; $length >= 4; $length--) {
                for ($index = 0; $index <= strlen($sequence) - $length; $index++) {
                    if (str_contains($normalized, substr($sequence, $index, $length))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
