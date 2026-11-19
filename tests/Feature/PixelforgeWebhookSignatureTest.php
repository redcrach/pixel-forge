<?php

beforeEach(function () {
    config(['pixelforge.webhook_secret' => 'whsec_test_secret', 'pixelforge.webhook_tolerance' => 300]);
});

$pingBody = '{"id":"evt_ping","object":"event","type":"ping","created_at":"2026-11-19T10:00:00Z"}';

test('a correctly signed webhook is accepted', function () use ($pingBody) {
    postPixelforgeWebhook($pingBody)->assertNoContent();
});

test('a webhook signed with another secret is rejected', function () use ($pingBody) {
    postPixelforgeWebhook($pingBody, pixelforgeSignature($pingBody, secret: 'whsec_someone_else'))
        ->assertUnauthorized();
});

test('a webhook whose body was changed after signing is rejected', function () use ($pingBody) {
    postPixelforgeWebhook(str_replace('evt_ping', 'evt_other', $pingBody), pixelforgeSignature($pingBody))
        ->assertUnauthorized();
});

test('a replayed webhook with an old timestamp is rejected', function () use ($pingBody) {
    $timestampSixMinutesAgo = now()->subMinutes(6)->getTimestamp();

    postPixelforgeWebhook($pingBody, pixelforgeSignature($pingBody, $timestampSixMinutesAgo))
        ->assertUnauthorized();
});

test('a webhook with a malformed signature header is rejected', function (string $signatureHeader) use ($pingBody) {
    postPixelforgeWebhook($pingBody, $signatureHeader)->assertBadRequest();
})->with([
    'empty' => [''],
    'no timestamp' => ['v1='.str_repeat('a', 64)],
    'no signature' => ['t=1762265009'],
    'not hex' => ['t=1762265009,v1='.str_repeat('z', 64)],
]);

test('webhooks are refused while no signing secret is configured', function () use ($pingBody) {
    config(['pixelforge.webhook_secret' => null]);

    postPixelforgeWebhook($pingBody, pixelforgeSignature($pingBody, secret: ''))
        ->assertServerError();
});
