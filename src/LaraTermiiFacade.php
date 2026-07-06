<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\Response balance()
 * @method static \Illuminate\Http\Client\Response history()
 * @method static \Illuminate\Http\Client\Response status(string $phoneNumber, string $countryCode)
 * @method static \Illuminate\Http\Client\Response search(string $phoneNumber)
 * @method static \Illuminate\Http\Client\Response allSenderId()
 * @method static \Illuminate\Http\Client\Response submitSenderId(string $senderId, string $useCase, string $company)
 * @method static \Illuminate\Http\Client\Response sendMessage(string $to, ?string $from, string $sms, ?string $channel = null, ?string $mediaUrl = null, ?string $mediaCaption = null, string $type = 'plain')
 * @method static \Illuminate\Http\Client\Response sendOTP(string $to, ?string $from, string $messageType, int $pinAttempts, int $pinTimeToLive, int $pinLength, string $pinPlaceholder, string $messageText, ?string $channel = null, ?string $pinType = null)
 * @method static \Illuminate\Http\Client\Response sendVoiceOTP(string $to, int $pinAttempts, int $pinTimeToLive, int $pinLength)
 * @method static \Illuminate\Http\Client\Response sendVoiceCall(string $to, int $code)
 * @method static \Illuminate\Http\Client\Response verifyOTP(string $pinId, string $pin)
 * @method static \Illuminate\Http\Client\Response sendInAppOTP(string $to, int $pinAttempts, int $pinTimeToLive, int $pinLength, string $pinType)
 *
 * @see LaraTermii
 */
class LaraTermiiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return LaraTermii::class;
    }
}
