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
- Run `$termii->status()` and pass appropriate params

### Verify phone numbers and automatically detect their status
- You can verify phone numbers and automatically detect their status.
- Run `$termii->search()` and pass appropriate params

### Retrieve the status of all registered sender ID
- You can retrieve the status of all registered sender ID.
- Run `$termii->allSenderId()`

### Request a new sender ID
- You can request a new sender ID.
- Run `$termii->submitSenderId()` and pass appropriate params

### Send OTP
- Coming soon

### OTP Validation
- Coming soon



### Security

If you discover any security related issues, please email adamsohiani@gmail.com instead of using the issue tracker.

## Credits

-   [Paul Adams](https://github.com/zeevx)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
