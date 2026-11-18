<?php

use App\Dto\Pricing\RatesDto;

test('rates are converted exactly to nanodollars per token', function () {
    $rates = RatesDto::fromArray([
        'input_text_tokens' => '5.00',
        'input_image_tokens' => '10.00',
        'output_tokens' => '40.00',
        'reasoning_tokens' => '12.50',
    ]);

    expect($rates->inputTextNano)->toBe(5_000)
        ->and($rates->inputImageNano)->toBe(10_000)
        ->and($rates->outputNano)->toBe(40_000)
        ->and($rates->reasoningNano)->toBe(12_500);
});

test('a rate finer than one nanodollar per token is rounded up', function () {
    $rates = RatesDto::fromArray([
        'input_text_tokens' => '5.0001',
        'input_image_tokens' => '10.00',
        'output_tokens' => '40.00',
        'reasoning_tokens' => '12.50',
    ]);

    expect($rates->inputTextNano)->toBe(5_001);
});

test('a malformed rate is rejected', function () {
    RatesDto::fromArray([
        'input_text_tokens' => '1e3',
        'input_image_tokens' => '10.00',
        'output_tokens' => '40.00',
        'reasoning_tokens' => '12.50',
    ]);
})->throws(UnexpectedValueException::class);
