<p align="center">
    <img title="Termii" src="https://termii.com/assets/images/logo.png"/>
</p>

## Termii Laravel Package
Lara-Termii helps you Set up, test, and manage your Termii integration directly in your Laravel App.

[![Total Downloads](https://img.shields.io/packagist/dt/zeevx/lara-termii.svg?style=flat-square)](https://packagist.org/packages/zeevx/lara-termii)


## Installation

You can install the package via composer:

```bash
composer require zeevx/lara-termii
```


## Usage:

### Declare Instance of Class
- Example `$termii = new \Zeevx\LaraTermii\LaraTermii("YOUR-TERMII-API-KEY");`

###  Check your balance on Termii
- You can check your termii balance.
- Run `$termii->balance()`

### Reports for messages sent across the sms, voice & whatsapp channels
- You can check reports for messages sent across the sms, voice & whatsapp channels.
- Run `$termii->history()`

### Detect if a number is fake or has ported to a new network
- You can check if a number is fake or has ported to a new network.
- Run `$termii->status(int $phone_number, string $country_code) ` and pass appropriate params

### Verify phone numbers and automatically detect their status
- You can verify phone numbers and automatically detect their status.
- Run `$termii->search(int $phone_number) ` and pass appropriate params

### Retrieve the status of all registered sender ID
- You can retrieve the status of all registered sender ID.
- Run `$termii->allSenderId()`

### Request a new sender ID
- You can request a new sender ID.
- Run `$termii->submitSenderId(string $sender_id, string $use_case, string $company)` and pass appropriate params

### Send Message
- You can a message.
- Run `$termii->sendMessage(int $to, string $from, string $sms, string $channel = "generic", bool $media = false, string $media_url = null, string $media_caption = null)` and pass appropriate params

### Send OTP
- You can send OTP
- Run `$termii->sendOTP(int $to, string $from, string $message_type, int $pin_attempts, int $pin_time_to_live, int $pin_length, string $pin_placeholder, string $message_text, string $channel = "generic")` and pass appropriate params

### Send Voice OTP
- You can send OTP
- Run `$termii->sendVoiceOTP(int $to, int $pin_attempts, int $pin_time_to_live, int $pin_length)` and pass appropriate params

### Send Voice Call
- You can send OTP
- Run `$termii->sendVoiceCall(int $to, int $code)` and pass appropriate params

### OTP Validation
- You can verify or validate OTP
- Run `$termii->verifyOTP(string $pinId, string $pin) ` pass appropriate params

### Send In-App OTP
- You can send In-App OTP
- Run `$termii->sendInAppOTP(int $to, int $pin_attempts, int $pin_time_to_live, int $pin_length, string $pin_type)` and pass appropriate params


### Security

If you discover any security related issues, please email adamsohiani@gmail.com instead of using the issue tracker.

## Credits

-   [Paul Adams](https://github.com/zeevx)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
