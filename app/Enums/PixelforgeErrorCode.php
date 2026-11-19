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
}
