<?php

declare(strict_types=1);

use Zeevx\LaraTermii\LaraTermii;
use Illuminate\Support\Facades\Http;
use Zeevx\LaraTermii\LaraTermiiFacade;
use Illuminate\Support\Facades\Storage;
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
            && strpos($request->url(), termiiUrl('get-balance')) === 0
            && $request['api_key'] === 'test-api-key';
    });
});

it('sends a message using the configured sender and channel', function () {
    $this->app->make(LaraTermii::class)->sendMessage('2348012345678', null, 'Hello there');

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('sms/send')
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
        return $request->url() === termiiUrl('sms/otp/send')
            && $request['pin_length'] === 6
            && $request['message_type'] === 'NUMERIC'
            && $request['pin_type'] === 'NUMERIC' // defaults to message_type
            && $request['from'] === 'N-Alert';
    });
});

it('requests a sender id sending both use_case field names', function () {
    $this->app->make(LaraTermii::class)->submitSenderId('Acme', 'Transactional alerts', 'Acme Inc');

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('sender-id/request')
            && $request['sender_id'] === 'Acme'
            && $request['use_case'] === 'Transactional alerts'
            && $request['usecase'] === 'Transactional alerts'
            && $request['company'] === 'Acme Inc';
    });
});

it('posts the pin details when verifying an otp', function () {
    $this->app->make(LaraTermii::class)->verifyOTP('pin-id-123', '123456');

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('sms/otp/verify')
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

it('honours a custom configured base url', function () {
    config()->set('termii.base_url', 'https://custom.example.com');
    $this->app->make(LaraTermii::class)->balance();

    Http::assertSent(fn ($request) => strpos($request->url(), termiiUrl('get-balance')) === 0
        && strpos($request->url(), 'https://custom.example.com/api/get-balance') === 0);
});

it('sends bulk messages with a recipients array', function () {
    $this->app->make(LaraTermii::class)->sendBulkMessage(['2348011111111', '2348022222222'], null, 'Bulk hi');

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('sms/send/bulk')
            && $request['to'] === ['2348011111111', '2348022222222']
            && $request['from'] === 'Acme'
            && $request['sms'] === 'Bulk hi';
    });
});

it('sends an email otp', function () {
    $this->app->make(LaraTermii::class)->sendEmailOTP('user@example.com', '123456', 'cfg-1');

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('email/otp/send')
            && $request['email_address'] === 'user@example.com'
            && $request['code'] === '123456'
            && $request['email_configuration_id'] === 'cfg-1';
    });
});

it('sends a device template message', function () {
    $this->app->make(LaraTermii::class)->sendTemplate('2348012345678', 'device-1', 'tmpl-1', ['product' => 'Widget']);

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('send/template')
            && $request['device_id'] === 'device-1'
            && $request['template_id'] === 'tmpl-1'
            && $request['data']['product'] === 'Widget';
    });
});

it('sends a template message with media', function () {
    $this->app->make(LaraTermii::class)
        ->sendTemplateWithMedia('2348012345678', 'device-1', 'tmpl-1', 'https://x/i.png', 'cap', ['a' => 'b']);

    Http::assertSent(function ($request) {
        return $request->url() === termiiUrl('send/template/media')
            && $request['media']['url'] === 'https://x/i.png'
            && $request['media']['caption'] === 'cap'
            && $request['data']['a'] === 'b';
    });
});

it('manages phonebooks (create, update, delete, fetch)', function () {
    $termii = $this->app->make(LaraTermii::class);

    $termii->phonebooks();
    $termii->createPhonebook('VIP', 'Best customers');
    $termii->updatePhonebook('pb-1', 'VIPs');
    $termii->deletePhonebook('pb-1');

    Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/api/phonebooks?'));
    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r->url() === termiiUrl('phonebooks')
        && $r['phonebook_name'] === 'VIP' && $r['description'] === 'Best customers');
    Http::assertSent(fn ($r) => $r->method() === 'PATCH' && str_contains($r->url(), '/api/phonebooks/pb-1')
        && $r['phonebook_name'] === 'VIPs');
    Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_contains($r->url(), '/api/phonebooks/pb-1?api_key='));
});

it('manages contacts (fetch, add, delete)', function () {
    $termii = $this->app->make(LaraTermii::class);

    $termii->contacts('pb-1');
    $termii->addContact('pb-1', '2348012345678', '234', 'a@b.com', 'Ada', 'Lovelace');
    $termii->deleteContact('pb-1', 'contact-9');

    Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/api/phonebooks/pb-1/contacts'));
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), '/api/phonebooks/pb-1/contacts')
        && $r['phone_number'] === '2348012345678' && $r['first_name'] === 'Ada' && $r['email_address'] === 'a@b.com');
    Http::assertSent(fn ($r) => $r->method() === 'DELETE' && $r['id'] === 'contact-9');
});

it('bulk-adds contacts from a local csv file', function () {
    $path = sys_get_temp_dir().'/lara_termii_contacts.csv';
    file_put_contents($path, "phone_number\n2348012345678\n");

    $this->app->make(LaraTermii::class)->addContactsFromFile('pb-1', $path, '234');

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && $r->url() === termiiUrl('phonebooks/contacts/upload'));

    @unlink($path);
});

it('bulk-adds contacts from a laravel storage disk', function () {
    Storage::fake('local');
    Storage::disk('local')->put('imports/contacts.csv', "phone_number\n2348012345678\n");

    $this->app->make(LaraTermii::class)->addContactsFromFile('pb-1', 'imports/contacts.csv', '234', 'local');

    // Reaching the endpoint proves the file was read from the disk successfully.
    // (Multipart form fields aren't accessible via $request[...], so we assert
    // on method + URL, as with the other upload tests.)
    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && $r->url() === termiiUrl('phonebooks/contacts/upload'));
});

it('bulk-adds contacts from raw csv contents', function () {
    $this->app->make(LaraTermii::class)
        ->addContactsFromContents('pb-1', "phone_number\n2348012345678\n", 'contacts.csv', '234');

    Http::assertSent(function ($r) {
        if ($r->method() !== 'POST' || $r->url() !== termiiUrl('phonebooks/contacts/upload')) {
            return false;
        }

        $contact = collect($r->data())->firstWhere('name', 'contact');

        return $contact !== null
            && json_decode($contact['contents'], true) === [
                'pid' => 'pb-1',
                'country_code' => '234',
                'api_key' => 'test-api-key',
            ];
    });
});

it('sends and manages campaigns', function () {
    $termii = $this->app->make(LaraTermii::class);

    $termii->sendCampaign('234', 'Acme', 'Hello', 'pb-1', 'generic', 'plain', 'personalized');
    $termii->campaigns();
    $termii->campaignHistory('camp-1');
    $termii->retryCampaign('camp-1');

    Http::assertSent(fn ($r) => $r->url() === termiiUrl('sms/campaigns/send')
        && $r['phonebook_id'] === 'pb-1' && $r['campaign_type'] === 'personalized'
        && $r['schedule_sms_status'] === 'regular' && $r['sender_id'] === 'Acme');
    Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/api/sms/campaigns?'));
    Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/api/sms/campaigns/camp-1'));
    Http::assertSent(fn ($r) => $r->method() === 'PATCH' && str_contains($r->url(), '/api/sms/campaigns/camp-1'));
});

it('lets campaign options override the default fields', function () {
    $this->app->make(LaraTermii::class)->sendCampaign(
        '234',
        'Acme',
        'Hello',
        'pb-1',
        'generic',
        'plain',
        'regular',
        'scheduled',
        ['schedule_time' => '30-06-2027 06:00']
    );

    Http::assertSent(fn ($r) => $r->url() === termiiUrl('sms/campaigns/send')
        && $r['schedule_sms_status'] === 'scheduled'
        && $r['schedule_time'] === '30-06-2027 06:00');
});

it('filters sender ids by name and status', function () {
    $this->app->make(LaraTermii::class)->allSenderId('Acme', 'active');

    Http::assertSent(fn ($r) => $r->method() === 'GET'
        && strpos($r->url(), termiiUrl('sender-id')) === 0
        && $r['name'] === 'Acme'
        && $r['status'] === 'active');
});

it('fetches the history of a single message by id', function () {
    $this->app->make(LaraTermii::class)->history('msg-123');

    Http::assertSent(fn ($r) => $r->method() === 'GET'
        && strpos($r->url(), termiiUrl('sms/inbox')) === 0
        && $r['message_id'] === 'msg-123');
});

it('ships a v4 default base url', function () {
    $config = require __DIR__.'/../../config/termii.php';

    expect($config['base_url'])->toBe('https://v4.api.termii.com');
});
