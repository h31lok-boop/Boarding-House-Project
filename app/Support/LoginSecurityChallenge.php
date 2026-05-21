<?php

namespace App\Support;

use Illuminate\Contracts\Session\Session;

class LoginSecurityChallenge
{
    public const QUESTION_KEY = 'login_security_question';

    private const ANSWER_KEY = 'login_security_answer_hash';

    public static function ensure(Session $session): string
    {
        if (! $session->has(self::QUESTION_KEY) || ! $session->has(self::ANSWER_KEY)) {
            return self::regenerate($session);
        }

        return (string) $session->get(self::QUESTION_KEY);
    }

    public static function regenerate(Session $session): string
    {
        $left = random_int(2, 9);
        $right = random_int(2, 9);
        $answer = (string) ($left + $right);

        $question = "What is {$left} + {$right}?";

        $session->put(self::QUESTION_KEY, $question);
        $session->put(self::ANSWER_KEY, self::hashAnswer($answer));

        return $question;
    }

    public static function verify(Session $session, mixed $answer): bool
    {
        self::ensure($session);

        $expected = (string) $session->get(self::ANSWER_KEY);
        $given = self::hashAnswer(trim((string) $answer));

        return hash_equals($expected, $given);
    }

    public static function clear(Session $session): void
    {
        $session->forget([
            self::QUESTION_KEY,
            self::ANSWER_KEY,
        ]);
    }

    private static function hashAnswer(string $answer): string
    {
        return hash_hmac('sha256', $answer, (string) config('app.key'));
    }
}
