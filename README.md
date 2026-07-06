<p align="center">
    <img title="Termii" src="https://termii.com/assets/images/logo.png"/>
</p>

# Lara-Termii

A modern Laravel package for the [Termii](https://www.termii.com) messaging, voice & OTP API. Set up, test, and manage your Termii integration directly in your Laravel app.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zeevx/lara-termii.svg?style=flat-square)](https://packagist.org/packages/zeevx/lara-termii)
[![Total Downloads](https://img.shields.io/packagist/dt/zeevx/lara-termii.svg?style=flat-square)](https://packagist.org/packages/zeevx/lara-termii)
[![License](https://img.shields.io/packagist/l/zeevx/lara-termii.svg?style=flat-square)](LICENSE.md)

## Requirements

- PHP 7.4+ (works through PHP 8.4)
- Laravel 7 through 13

> Laravel 6 is not supported: it predates Laravel's HTTP client
> (`Illuminate\Http\Client`), which this package is built on.

> **Note for PHP 7.4 users:** the examples below use PHP 8.0 named arguments for
> readability. On PHP 7.4 pass the arguments positionally instead, e.g.
> `$termii->sendMessage('2348012345678', null, 'Hello');`

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
# Optional – Termii now issues an account-specific base URL (see your dashboard)
TERMII_BASE_URL=https://v3.api.termii.com
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
$termii = new LaraTermii('another-api-key', 'https://v3.api.termii.com');
```

### Check your balance

```php
$termii->balance();
```

### Message history

Reports for messages sent across the sms, voice & whatsapp channels.

```php
$termii->history();
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

> The test suite runs on the full support range (PHP 7.4+ / Laravel 7+), but
> Pint itself requires PHP 8.1+. It is a dev-only dependency and never affects
> what your application needs. Contribute on PHP 8.1+ to use it; the CI test
> matrix removes Pint on the PHP 7.4/8.0 legs so tests still run there.

## Upgrading from v1

v2 is a modernized rewrite. Key changes:

- **Return types**: methods now return `Illuminate\Http\Client\Response` instead
  of a raw JSON string. Call `->json()` / `->body()` to get the old data.
- **Config-driven**: the facade and container binding now work out of the box.
  Set `TERMII_API_KEY` (v1's binding required a constructor argument and could
  fatal). `new LaraTermii()` with no arguments reads from config.
- **Phone numbers are strings** (were `int`), so international/`+`-prefixed
  numbers are preserved.
- **`sendMessage`**: the first four positional arguments (`to`, `from`, `sms`,
  `channel`) are unchanged, but the old (broken) `bool $media` flag was removed.
  Any v1 call that passed the media flag and URL positionally, e.g.
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
