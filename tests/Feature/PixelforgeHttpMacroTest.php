<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('the pixelforge macro sends authenticated json requests to the configured base url', function () {
    config([
        'pixelforge.base_url' => 'https://pixelforge.test/v1',
        'pixelforge.api_key' => 'pk_test_123',
    ]);
    Http::fake();

    Http::pixelforge()->get('/pricing');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://pixelforge.test/v1/pricing'
        && $request->hasHeader('Authorization', 'Bearer pk_test_123')
        && $request->hasHeader('Accept', 'application/json')
    );
});
