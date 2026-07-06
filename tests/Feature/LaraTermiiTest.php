<?php

declare(strict_types=1);

use Zeevx\LaraTermii\LaraTermii;
use Illuminate\Support\Facades\Http;
use Zeevx\LaraTermii\LaraTermiiFacade;
use Zeevx\LaraTermii\Exceptions\TermiiException;

beforeEach(function () {
    Http::fake([
        '*' => Http::response(['status' => 'ok'], 200),
    ]);
});

it('resolves from the container using config', function () {
    expect($this->app->make(LaraTermii::class))->toBeInstanceOf(LaraTermii::class);
});

it('throws when the api key is missing', function () {
    expect(fn () => new LaraTermii(''))->toThrow(TermiiException::class);
});

it('hits the balance endpoint with the api key', function () {
    $response = $this->app->make(LaraTermii::class)->balance();

    expect($response->successful())->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && strpos($request->url(), 'https://v3.api.termii.com/api/get-balance') === 0
            && $request['api_key'] === 'test-api-key';
    });
});

it('sends a message using the configured sender and channel', function () {
    $this->app->make(LaraTermii::class)->sendMessage('2348012345678', null, 'Hello there');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://v3.api.termii.com/api/sms/send'
            && $request['to'] === '2348012345678'
            && $request['from'] === 'Acme'
            && $request['sms'] === 'Hello there'
            && $request['channel'] === 'generic'
            && $request['type'] === 'plain'
            && $request['api_key'] === 'test-api-key';
    });
});

it('switches to whatsapp when a media url is supplied', function () {
    $this->app->make(LaraTermii::class)->sendMessage(
        '2348012345678',
        'device',
        'caption me',
        null,
        'https://example.com/img.png',
        'A caption'
    );

    Http::assertSent(function ($request) {
        return $request['channel'] === 'whatsapp'
            && $request['media']['url'] === 'https://example.com/img.png'
            && $request['media']['caption'] === 'A caption'
            && ! array_key_exists('sms', $request->data()); // media requests must not include "sms"
    });
});

it('throws when sending a message without a sender', function () {
    config()->set('termii.sender_id', null);
    $termii = $this->app->make(LaraTermii::class);

    expect(fn () => $termii->sendMessage('2348012345678', null, 'Hello'))
        ->toThrow(TermiiException::class);
});

it('posts the expected payload when sending an otp', function () {
    $this->app->make(LaraTermii::class)
        ->sendOTP('2348012345678', 'N-Alert', 'NUMERIC', 3, 5, 6, '< 1234 >', 'Your pin is < 1234 >');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://v3.api.termii.com/api/sms/otp/send'
            && $request['pin_length'] === 6
            && $request['message_type'] === 'NUMERIC'
            && $request['pin_type'] === 'NUMERIC' // defaults to message_type
            && $request['from'] === 'N-Alert';
    });
});

it('requests a sender id sending both use_case field names', function () {
    $this->app->make(LaraTermii::class)->submitSenderId('Acme', 'Transactional alerts', 'Acme Inc');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://v3.api.termii.com/api/sender-id/request'
            && $request['sender_id'] === 'Acme'
            && $request['use_case'] === 'Transactional alerts'
            && $request['usecase'] === 'Transactional alerts'
            && $request['company'] === 'Acme Inc';
    });
});

it('posts the pin details when verifying an otp', function () {
    $this->app->make(LaraTermii::class)->verifyOTP('pin-id-123', '123456');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://v3.api.termii.com/api/sms/otp/verify'
            && $request['pin_id'] === 'pin-id-123'
            && $request['pin'] === '123456';
    });
});

it('works through the facade', function () {
    LaraTermiiFacade::balance();

    Http::assertSent(function ($request) {
        return strpos($request->url(), '/api/get-balance') !== false;
    });
});
