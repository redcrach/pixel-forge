<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only lets through webhooks signed by Pixelforge with this environment's secret.
 * The header is `t=<unix timestamp>,v1=<hex HMAC-SHA256 of "{t}.{raw body}">`.
 */
class VerifyPixelforgeSignature
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signatureHeader = $this->parseSignatureHeader((string) $request->header('Pixelforge-Signature'));

        if ($signatureHeader === null) {
            abort(400, 'Malformed Pixelforge-Signature header.');
        }

        if ($this->isStale($signatureHeader['timestamp'])) {
            abort(401, 'Stale Pixelforge signature.');
        }

        $expectedSignature = hash_hmac('sha256', "{$signatureHeader['timestamp']}.{$request->getContent()}", $this->secret());

        if (! hash_equals($expectedSignature, $signatureHeader['signature'])) {
            abort(401, 'Invalid Pixelforge signature.');
        }

        return $next($request);
    }

    /**
     * @return array{timestamp: int, signature: string}|null
     */
    private function parseSignatureHeader(string $header): ?array
    {
        if (preg_match('/^t=(\d+),v1=([0-9a-f]{64})$/', $header, $matches) !== 1) {
            return null;
        }

        return ['timestamp' => (int) $matches[1], 'signature' => $matches[2]];
    }

    /**
     * Each delivery attempt is signed with a fresh timestamp, so an old one means a replayed request.
     */
    private function isStale(int $timestamp): bool
    {
        return abs(now()->getTimestamp() - $timestamp) > (int) config('pixelforge.webhook_tolerance');
    }

    /**
     * Fails closed: without a secret, anyone could sign a webhook with an empty key.
     */
    private function secret(): string
    {
        $secret = (string) config('pixelforge.webhook_secret');

        if ($secret === '') {
            throw new RuntimeException('PIXELFORGE_WEBHOOK_SECRET is not set.');
        }

        return $secret;
    }
}
