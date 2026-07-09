# Lara-Termii

A modern Laravel package for the [Termii](https://www.termii.com) messaging, voice & OTP API. Set up, test, and manage your Termii integration directly in your Laravel app.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zeevx/lara-termii.svg?style=flat-square)](https://packagist.org/packages/zeevx/lara-termii)
[![Total Downloads](https://img.shields.io/packagist/dt/zeevx/lara-termii.svg?style=flat-square)](https://packagist.org/packages/zeevx/lara-termii)
[![License](https://img.shields.io/packagist/l/zeevx/lara-termii.svg?style=flat-square)](LICENSE.md)

## Requirements

- PHP 8.1 through 8.4
- Laravel 9 through 13

> Still on an older stack? Lara-Termii **0.1.x** supports Laravel 6–9 / PHP 7.4+.

## Installation

Install via composer:

```bash
composer require zeevx/lara-termii
```

Publish the config file:

```bash
php artisan vendor:publish --tag="termii-config"
```

Then add your Termii credentials to your `.env` file:

```dotenv
TERMII_API_KEY=your-termii-api-key
TERMII_SENDER_ID=YourSenderID
# Optional: Termii issues an account-specific, region-based base URL.
# Find yours on your dashboard and set it here (defaults to the v4 host).
TERMII_BASE_URL=https://v4.api.termii.com
# Optional defaults
TERMII_CHANNEL=generic
TERMII_TIMEOUT=30
TERMII_THROW=false
```

## Usage

Every method returns an `Illuminate\Http\Client\Response`, so you get the full
power of Laravel's HTTP client: `->json()`, `->successful()`, `->failed()`,
`->status()`, `->body()` and more.

You can use the package in three interchangeable ways.

**Facade:**

```php
use Zeevx\LaraTermii\LaraTermiiFacade as Termii;

$balance = Termii::balance()->json();
```

**Dependency injection / container:**

```php
use Zeevx\LaraTermii\LaraTermii;

public function show(LaraTermii $termii)
{
    return $termii->balance()->json();
}
```

**Manual instantiation** (credentials fall back to config when omitted):

```php
use Zeevx\LaraTermii\LaraTermii;

$termii = new LaraTermii(); // uses config('termii.*')
// or override per instance:
$termii = new LaraTermii('another-api-key', 'https://v4.api.termii.com');
```

### Check your balance

```php
$termii->balance();
```

### Message history

Reports for messages sent across the sms, voice & whatsapp channels.

```php
$termii->history();

// or a single message's report
$termii->history(messageId: 'message-id');
```

### Verify a number & detect its network

```php
$termii->status(phoneNumber: '2348012345678', countryCode: 'NG');
```

### DND (Do Not Disturb) lookup

```php
$termii->search(phoneNumber: '2348012345678');
```

### Sender IDs

```php
// Retrieve the status of all registered Sender IDs
$termii->allSenderId();

// Optionally filter by name and/or status ("active", "pending" or "blocked")
$termii->allSenderId(name: 'Acme', status: 'active');

// Request a new Sender ID
$termii->submitSenderId(senderId: 'Acme', useCase: 'Transactional alerts', company: 'Acme Inc');
```

### Send a message

`from` may be `null` to fall back to your configured `TERMII_SENDER_ID`.

```php
$termii->sendMessage(
    to: '2348012345678',
    from: null,            // falls back to config('termii.sender_id')
    sms: 'Hello from Lara-Termii!'
);
```

Send a WhatsApp media message. Passing a media URL automatically switches the
message to the WhatsApp channel:

```php
$termii->sendMessage(
    to: '2348012345678',
    from: 'your-whatsapp-device',
    sms: 'Check this out',
    channel: 'whatsapp',
    mediaUrl: 'https://example.com/image.png',
    mediaCaption: 'An optional caption'
);
```

### Send OTP

```php
$termii->sendOTP(
    to: '2348012345678',
    from: null,
    messageType: 'NUMERIC',
    pinAttempts: 3,
    pinTimeToLive: 5,
    pinLength: 6,
    pinPlaceholder: '< 1234 >',
    messageText: 'Your confirmation code is < 1234 >'
);
```

### Send Voice OTP

```php
$termii->sendVoiceOTP(to: '2348012345678', pinAttempts: 3, pinTimeToLive: 5, pinLength: 6);
```

### Send Voice Call

```php
$termii->sendVoiceCall(to: '2348012345678', code: 123456);
```

### Verify OTP

```php
$response = $termii->verifyOTP(pinId: 'pin-id-from-send-otp', pin: '123456');

if ($response->json('verified') === true) {
    // OTP is valid
}
```

### In-App OTP

```php
$termii->sendInAppOTP(
    to: '2348012345678',
    pinAttempts: 3,
    pinTimeToLive: 5,
    pinLength: 6,
    pinType: 'NUMERIC'
);
```

### Email OTP

Note: email OTPs cannot be verified with `verifyOTP()`.

```php
$termii->sendEmailOTP(
    emailAddress: 'user@example.com',
    code: '123456',
    emailConfigurationId: 'your-email-config-id'
);
```

### Send bulk messages

Send the same message to up to 100 recipients at once.

```php
$termii->sendBulkMessage(
    to: ['2348011111111', '2348022222222'],
    from: null,
    sms: 'Hello everyone!'
);
```

### WhatsApp device templates

```php
// Plain template
$termii->sendTemplate(
    to: '2348012345678',
    deviceId: 'your-device-id',
    templateId: 'your-template-id',
    data: ['product_name' => 'Widget', 'otp' => '1234']
);

// Template with a media attachment
$termii->sendTemplateWithMedia(
    to: '2348012345678',
    deviceId: 'your-device-id',
    templateId: 'your-template-id',
    mediaUrl: 'https://example.com/image.png',
    mediaCaption: 'Optional caption',
    data: ['product_name' => 'Widget']
);
```

### Phonebooks

```php
$termii->phonebooks();                                  // fetch all
$termii->createPhonebook(name: 'VIP', description: 'Best customers');
$termii->updatePhonebook(phonebookId: 'pb-id', name: 'VIPs');
$termii->deletePhonebook(phonebookId: 'pb-id');
```

### Contacts

```php
$termii->contacts(phonebookId: 'pb-id');                // fetch all in a phonebook

$termii->addContact(
    phonebookId: 'pb-id',
    phoneNumber: '2348012345678',
    countryCode: '234',
    emailAddress: 'ada@example.com',
    firstName: 'Ada',
    lastName: 'Lovelace'
);

// Bulk-add from a CSV file on the local filesystem
$termii->addContactsFromFile(phonebookId: 'pb-id', file: '/path/contacts.csv', countryCode: '234');

// ...or from any Laravel filesystem disk (s3, local, public, ...)
$termii->addContactsFromFile(phonebookId: 'pb-id', file: 'imports/contacts.csv', countryCode: '234', disk: 's3');

// ...or straight from an uploaded file (raw contents)
$csv = $request->file('csv');
$termii->addContactsFromContents(
    phonebookId: 'pb-id',
    contents: $csv->get(),
    filename: $csv->getClientOriginalName(),
    countryCode: '234'
);

$termii->deleteContact(phonebookId: 'pb-id', contactId: 'contact-id');
```

### Campaigns

```php
$termii->sendCampaign(
    countryCode: '234',
    senderId: 'Acme',
    message: 'Welcome to Acme.',
    phonebookId: 'pb-id',
    channel: 'generic',
    messageType: 'plain',
    campaignType: 'personalized',      // "regular" or "personalized"
    scheduleSmsStatus: 'regular',      // "regular" or "scheduled"
    options: [
        'remove_duplicate' => 'yes',
        // 'schedule_time' => '30-06-2026 6:00', // required when scheduled
    ]
);

$termii->campaigns();                          // fetch all
$termii->campaignHistory(campaignId: 'camp-id');
$termii->retryCampaign(campaignId: 'camp-id');
```

### eSIMs (Sotel)

Termii's eSIM API uses its own bearer-token authentication, so it is exposed
as a sub-client. It shares your configured API key and base URL, exchanges the
key for a token on the first call, and reuses that token for the lifetime of
the instance. Call `authenticate()` yourself only to refresh an expired token.

```php
$esim = $termii->esim();

$esim->dataPlans(country: 'NG', type: 'LOCAL');   // both filters optional
$esim->createEsim(productId: 'prod-id', iso3: 'NGA');
$esim->purchasePlan(iccid: '894...', productId: 'prod-id', iso3: 'NGA');
$esim->qrCode(iccid: '894...');
$esim->profile(iccid: '894...');
$esim->usage(iccid: '894...');
$esim->planStatus(iccid: '894...');
$esim->esims(page: 0, size: 15);
$esim->countries(page: 0, size: 15);
```

## Error handling

By default a failed request returns the `Response` so you can inspect it:

```php
$response = $termii->balance();

if ($response->failed()) {
    report($response->json('message'));
}
```

Set `TERMII_THROW=true` (or `config('termii.throw')`) to have any 4xx/5xx
response throw an `Illuminate\Http\Client\RequestException` instead.

## Testing

Because the package uses Laravel's HTTP client, you can fake Termii in your own
tests:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    '*/api/sms/send' => Http::response(['message' => 'Successfully Sent'], 200),
]);
```

Run the package test suite (powered by [Pest](https://pestphp.com)):

```bash
composer test
```

## Contributing

The dev toolchain uses [Pest](https://pestphp.com) for tests and
[Laravel Pint](https://laravel.com/docs/pint) for code style.

```bash
composer test    # run the test suite
composer lint    # check code style (pint --test)
composer format  # fix code style (pint)
```

> Pint and Pest are dev-only dependencies and never affect what your
> application needs at runtime.

## Upgrading from 0.1.x

1.0.0 is the first stable release and a modernized rewrite. Key changes from the
0.1.x line:

- **Return types**: methods now return `Illuminate\Http\Client\Response` instead
  of a raw JSON string. Call `->json()` / `->body()` to get the old data.
- **Config-driven**: the facade and container binding now work out of the box.
  Set `TERMII_API_KEY` (the 0.1.x binding required a constructor argument and
  could fatal). `new LaraTermii()` with no arguments reads from config.
- **Phone numbers are strings** (were `int`), so international/`+`-prefixed
  numbers are preserved.
- **`sendMessage`**: the first four positional arguments (`to`, `from`, `sms`,
  `channel`) are unchanged, but the old (broken) `bool $media` flag was removed.
  Any 0.1.x call that passed the media flag and URL positionally, e.g.
  `sendMessage($to, $from, $sms, 'whatsapp', true, $url, $caption)`, must be
  updated to `sendMessage($to, $from, $sms, 'whatsapp', $url, $caption)`. Media
  now works correctly, and a media request no longer sends the `sms` field (per
  Termii's docs).
- **`sendOTP`**: now also sends `pin_type` (required by Termii's send-token
  endpoint), defaulting to the `message_type` you pass, so existing calls keep
  working. An optional `$pinType` argument was added at the end if you need to
  set it separately.
- **`from`** now accepts `null` on `sendMessage()` / `sendOTP()` to fall back to
  your configured Sender ID.

## Security

If you discover any security-related issues, please email adamsohiani@gmail.com
instead of using the issue tracker.

## Credits

- [Paul Adams](https://github.com/zeevx)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
