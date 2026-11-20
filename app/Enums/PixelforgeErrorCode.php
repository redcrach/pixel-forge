<?php

namespace App\Enums;

/**
 * The `error.code` values Pixelforge answers POST /v1/generations with.
 */
enum PixelforgeErrorCode: string
{
    case INVALID_API_KEY = 'invalid_api_key';
    case VALIDATION_FAILED = 'validation_failed';
    case CONTENT_POLICY_VIOLATION = 'content_policy_violation';
    case IDEMPOTENCY_KEY_REUSED = 'idempotency_key_reused';
    case RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    case CONCURRENCY_LIMIT_EXCEEDED = 'concurrency_limit_exceeded';
    case DAILY_LIMIT_EXCEEDED = 'daily_limit_exceeded';
    case OVERLOADED = 'overloaded';

    /**
     * What we tell the user when Pixelforge refuses a generation for good. Only the reasons a user can act
     * on get their own message; anything else (a bad API key, a bug in our request) is our problem.
     */
    public function userMessage(): string
    {
        return match ($this) {
            self::CONTENT_POLICY_VIOLATION => __('This prompt was blocked by the content policy. Try rewording it.'),
            self::DAILY_LIMIT_EXCEEDED => __('The daily generation limit has been reached. Please try again tomorrow.'),
            default => self::genericMessage(),
        };
    }

    public static function genericMessage(): string
    {
        return __('Something went wrong on our side, so the image could not be generated.');
    }
}
