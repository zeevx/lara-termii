<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\Response balance()
 * @method static \Illuminate\Http\Client\Response history(?string $messageId = null)
 * @method static \Illuminate\Http\Client\Response status(string $phoneNumber, string $countryCode)
 * @method static \Illuminate\Http\Client\Response search(string $phoneNumber)
 * @method static \Illuminate\Http\Client\Response allSenderId(?string $name = null, ?string $status = null)
 * @method static \Illuminate\Http\Client\Response submitSenderId(string $senderId, string $useCase, string $company)
 * @method static \Illuminate\Http\Client\Response sendMessage(string $to, ?string $from, string $sms, ?string $channel = null, ?string $mediaUrl = null, ?string $mediaCaption = null, string $type = 'plain')
 * @method static \Illuminate\Http\Client\Response sendOTP(string $to, ?string $from, string $messageType, int $pinAttempts, int $pinTimeToLive, int $pinLength, string $pinPlaceholder, string $messageText, ?string $channel = null, ?string $pinType = null)
 * @method static \Illuminate\Http\Client\Response sendVoiceOTP(string $to, int $pinAttempts, int $pinTimeToLive, int $pinLength)
 * @method static \Illuminate\Http\Client\Response sendVoiceCall(string $to, int $code)
 * @method static \Illuminate\Http\Client\Response verifyOTP(string $pinId, string $pin)
 * @method static \Illuminate\Http\Client\Response sendInAppOTP(string $to, int $pinAttempts, int $pinTimeToLive, int $pinLength, string $pinType)
 * @method static \Illuminate\Http\Client\Response sendEmailOTP(string $emailAddress, string $code, string $emailConfigurationId)
 * @method static \Illuminate\Http\Client\Response sendBulkMessage(array $to, ?string $from, string $sms, ?string $channel = null, string $type = 'plain')
 * @method static \Illuminate\Http\Client\Response sendTemplate(string $to, string $deviceId, string $templateId, array $data = [])
 * @method static \Illuminate\Http\Client\Response sendTemplateWithMedia(string $to, string $deviceId, string $templateId, string $mediaUrl, ?string $mediaCaption = null, array $data = [])
 * @method static \Illuminate\Http\Client\Response phonebooks()
 * @method static \Illuminate\Http\Client\Response createPhonebook(string $name, ?string $description = null)
 * @method static \Illuminate\Http\Client\Response updatePhonebook(string $phonebookId, string $name, ?string $description = null)
 * @method static \Illuminate\Http\Client\Response deletePhonebook(string $phonebookId)
 * @method static \Illuminate\Http\Client\Response contacts(string $phonebookId)
 * @method static \Illuminate\Http\Client\Response addContact(string $phonebookId, string $phoneNumber, ?string $countryCode = null, ?string $emailAddress = null, ?string $firstName = null, ?string $lastName = null, ?string $company = null)
 * @method static \Illuminate\Http\Client\Response addContactsFromFile(string $phonebookId, string $file, string $countryCode, ?string $disk = null)
 * @method static \Illuminate\Http\Client\Response addContactsFromContents(string $phonebookId, string $contents, string $filename, string $countryCode)
 * @method static \Illuminate\Http\Client\Response deleteContact(string $phonebookId, string $contactId)
 * @method static \Illuminate\Http\Client\Response sendCampaign(string $countryCode, string $senderId, string $message, string $phonebookId, string $channel = 'generic', string $messageType = 'plain', string $campaignType = 'regular', string $scheduleSmsStatus = 'regular', array $options = [])
 * @method static \Illuminate\Http\Client\Response campaigns()
 * @method static \Illuminate\Http\Client\Response campaignHistory(string $campaignId)
 * @method static \Illuminate\Http\Client\Response retryCampaign(string $campaignId)
 * @method static \Zeevx\LaraTermii\LaraTermiiEsim esim()
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
