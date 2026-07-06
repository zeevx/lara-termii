<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\PendingRequest;
use Zeevx\LaraTermii\Exceptions\TermiiException;

class LaraTermii
{
    /**
     * The Termii API key.
     */
    protected string $apiKey;

    /**
     * The account base URL (without a trailing slash or the /api segment).
     */
    protected string $baseUrl;

    /**
     * Default Sender ID used when a "from" value is omitted.
     */
    protected ?string $senderId;

    /**
     * Default channel used when a "channel" value is omitted.
     */
    protected string $channel;

    /**
     * Request timeout in seconds.
     */
    protected int $timeout;

    /**
     * Whether failed responses should throw a RequestException.
     */
    protected bool $throw;

    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?string $senderId = null,
        ?string $channel = null,
        ?int $timeout = null,
        ?bool $throw = null
    ) {
        $this->apiKey = $apiKey ?? (string) config('termii.api_key');

        if ($this->apiKey === '') {
            throw TermiiException::missingApiKey();
        }

        $this->baseUrl = rtrim($baseUrl ?? (string) config('termii.base_url', 'https://v3.api.termii.com'), '/');
        $this->senderId = $senderId ?? config('termii.sender_id');
        $this->channel = $channel ?? (string) config('termii.channel', 'generic');
        $this->timeout = $timeout ?? (int) config('termii.timeout', 30);
        $this->throw = $throw ?? (bool) config('termii.throw', false);
    }

    /**
     * Build a configured HTTP client.
     */
    protected function client(): PendingRequest
    {
        return Http::timeout($this->timeout)->acceptJson();
    }

    /**
     * Build a fully-qualified endpoint URL.
     */
    protected function url(string $path): string
    {
        return $this->baseUrl.'/api/'.ltrim($path, '/');
    }

    /**
     * Optionally throw on a failed (4xx/5xx) response.
     */
    protected function maybeThrow(Response $response): Response
    {
        if ($this->throw) {
            $response->throw();
        }

        return $response;
    }

    /**
     * Perform a GET request, always sending the API key as a query param.
     *
     * @param  array<string, mixed>  $query
     */
    protected function get(string $path, array $query = []): Response
    {
        return $this->maybeThrow(
            $this->client()->get($this->url($path), array_merge(['api_key' => $this->apiKey], $query))
        );
    }

    /**
     * Perform a POST request, always sending the API key in the JSON body.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function post(string $path, array $payload = []): Response
    {
        return $this->maybeThrow(
            $this->client()->post($this->url($path), array_merge(['api_key' => $this->apiKey], $payload))
        );
    }

    /**
     * Perform a PATCH request, always sending the API key in the JSON body.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function patch(string $path, array $payload = []): Response
    {
        return $this->maybeThrow(
            $this->client()->patch($this->url($path), array_merge(['api_key' => $this->apiKey], $payload))
        );
    }

    /**
     * Perform a DELETE request. The API key (and any extra query params) are
     * sent in the query string; any payload is sent in the request body.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $payload
     */
    protected function delete(string $path, array $query = [], array $payload = []): Response
    {
        $url = $this->url($path).'?'.http_build_query(array_merge(['api_key' => $this->apiKey], $query));

        return $this->maybeThrow($this->client()->delete($url, $payload));
    }

    /**
     * Resolve the Sender ID, falling back to the configured default.
     */
    protected function resolveSender(?string $from): string
    {
        $from ??= $this->senderId;

        if ($from === null || $from === '') {
            throw TermiiException::missingSenderId();
        }

        return $from;
    }

    /*
    |--------------------------------------------------------------------------
    | Account
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve your total balance and wallet information.
     */
    public function balance(): Response
    {
        return $this->get('get-balance');
    }

    /**
     * Retrieve reports for messages sent across the sms, voice & whatsapp channels.
     */
    public function history(): Response
    {
        return $this->get('sms/inbox');
    }

    /**
     * Verify a phone number and detect its status and current network.
     */
    public function status(string $phoneNumber, string $countryCode): Response
    {
        return $this->get('insight/number/query', [
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
        ]);
    }

    /**
     * Check whether a phone number is on the DND (Do Not Disturb) list.
     */
    public function search(string $phoneNumber): Response
    {
        return $this->get('check/dnd', [
            'phone_number' => $phoneNumber,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Sender IDs
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve the status of all registered Sender IDs.
     */
    public function allSenderId(): Response
    {
        return $this->get('sender-id');
    }

    /**
     * Request a new Sender ID.
     *
     * Termii's docs are inconsistent about the field name for the use case
     * (the parameter table documents "use_case" while the SDK samples use
     * "usecase"), so we send both to stay compatible either way.
     */
    public function submitSenderId(string $senderId, string $useCase, string $company): Response
    {
        return $this->post('sender-id/request', [
            'sender_id' => $senderId,
            'use_case' => $useCase,
            'usecase' => $useCase,
            'company' => $company,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Messaging
    |--------------------------------------------------------------------------
    */

    /**
     * Send a message across any supported channel.
     *
     * Passing a media URL automatically switches the message to the WhatsApp
     * channel. Per Termii's docs, a media request must not also send the "sms"
     * field, so it is dropped in favour of the media object (and its caption).
     */
    public function sendMessage(
        string $to,
        ?string $from,
        string $sms,
        ?string $channel = null,
        ?string $mediaUrl = null,
        ?string $mediaCaption = null,
        string $type = 'plain'
    ): Response {
        $payload = [
            'to' => $to,
            'from' => $this->resolveSender($from),
            'sms' => $sms,
            'type' => $type,
            'channel' => $channel ?? $this->channel,
        ];

        if ($mediaUrl !== null) {
            unset($payload['sms']);
            $payload['channel'] = 'whatsapp';
            $payload['media'] = array_filter([
                'url' => $mediaUrl,
                'caption' => $mediaCaption,
            ], fn ($value) => $value !== null);
        }

        return $this->post('sms/send', $payload);
    }

    /*
    |--------------------------------------------------------------------------
    | Token / OTP
    |--------------------------------------------------------------------------
    */

    /**
     * Send a one-time PIN (OTP) to a recipient.
     *
     * Termii's send-token endpoint documents "pin_type" as required while its
     * examples also send "message_type"; we send both. "pin_type" defaults to
     * the given "message_type" (they are the same value in Termii's examples)
     * so existing callers keep working without passing an extra argument.
     */
    public function sendOTP(
        string $to,
        ?string $from,
        string $messageType,
        int $pinAttempts,
        int $pinTimeToLive,
        int $pinLength,
        string $pinPlaceholder,
        string $messageText,
        ?string $channel = null,
        ?string $pinType = null
    ): Response {
        return $this->post('sms/otp/send', [
            'to' => $to,
            'from' => $this->resolveSender($from),
            'message_type' => $messageType,
            'pin_type' => $pinType ?? $messageType,
            'channel' => $channel ?? $this->channel,
            'pin_attempts' => $pinAttempts,
            'pin_time_to_live' => $pinTimeToLive,
            'pin_length' => $pinLength,
            'pin_placeholder' => $pinPlaceholder,
            'message_text' => $messageText,
        ]);
    }

    /**
     * Send a one-time PIN (OTP) via a voice call.
     */
    public function sendVoiceOTP(string $to, int $pinAttempts, int $pinTimeToLive, int $pinLength): Response
    {
        return $this->post('sms/otp/send/voice', [
            'phone_number' => $to,
            'pin_attempts' => $pinAttempts,
            'pin_time_to_live' => $pinTimeToLive,
            'pin_length' => $pinLength,
        ]);
    }

    /**
     * Send a specific code to a recipient via a voice call.
     */
    public function sendVoiceCall(string $to, int $code): Response
    {
        return $this->post('sms/otp/call', [
            'phone_number' => $to,
            'code' => $code,
        ]);
    }

    /**
     * Verify a PIN that was previously sent.
     */
    public function verifyOTP(string $pinId, string $pin): Response
    {
        return $this->post('sms/otp/verify', [
            'pin_id' => $pinId,
            'pin' => $pin,
        ]);
    }

    /**
     * Generate an in-app OTP, returning the PIN details in the response body.
     */
    public function sendInAppOTP(string $to, int $pinAttempts, int $pinTimeToLive, int $pinLength, string $pinType): Response
    {
        return $this->post('sms/otp/generate', [
            'phone_number' => $to,
            'pin_type' => $pinType,
            'pin_attempts' => $pinAttempts,
            'pin_time_to_live' => $pinTimeToLive,
            'pin_length' => $pinLength,
        ]);
    }

    /**
     * Send an email OTP. Note: email OTPs cannot be verified with verifyOTP().
     */
    public function sendEmailOTP(string $emailAddress, string $code, string $emailConfigurationId): Response
    {
        return $this->post('email/otp/send', [
            'email_address' => $emailAddress,
            'code' => $code,
            'email_configuration_id' => $emailConfigurationId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk & Template Messaging
    |--------------------------------------------------------------------------
    */

    /**
     * Send the same message to many recipients (up to 100) at once.
     *
     * @param  array<int, string>  $to
     */
    public function sendBulkMessage(array $to, ?string $from, string $sms, ?string $channel = null, string $type = 'plain'): Response
    {
        return $this->post('sms/send/bulk', [
            'to' => array_values($to),
            'from' => $this->resolveSender($from),
            'sms' => $sms,
            'type' => $type,
            'channel' => $channel ?? $this->channel,
        ]);
    }

    /**
     * Send a message built from a pre-approved WhatsApp device template.
     *
     * @param  array<string, mixed>  $data  Values for the template variables.
     */
    public function sendTemplate(string $to, string $deviceId, string $templateId, array $data = []): Response
    {
        return $this->post('send/template', [
            'phone_number' => $to,
            'device_id' => $deviceId,
            'template_id' => $templateId,
            'data' => $data,
        ]);
    }

    /**
     * Send a WhatsApp device template message that includes a media attachment.
     *
     * @param  array<string, mixed>  $data  Values for the template variables.
     */
    public function sendTemplateWithMedia(
        string $to,
        string $deviceId,
        string $templateId,
        string $mediaUrl,
        ?string $mediaCaption = null,
        array $data = []
    ): Response {
        return $this->post('send/template/media', [
            'phone_number' => $to,
            'device_id' => $deviceId,
            'template_id' => $templateId,
            'data' => $data,
            'media' => array_filter([
                'url' => $mediaUrl,
                'caption' => $mediaCaption,
            ], fn ($value) => $value !== null),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Phonebooks
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve all phonebooks on your account.
     */
    public function phonebooks(): Response
    {
        return $this->get('phonebooks');
    }

    /**
     * Create a new phonebook.
     */
    public function createPhonebook(string $name, ?string $description = null): Response
    {
        return $this->post('phonebooks', array_filter([
            'phonebook_name' => $name,
            'description' => $description,
        ], fn ($value) => $value !== null));
    }

    /**
     * Update an existing phonebook.
     */
    public function updatePhonebook(string $phonebookId, string $name, ?string $description = null): Response
    {
        return $this->patch("phonebooks/{$phonebookId}", array_filter([
            'phonebook_name' => $name,
            'description' => $description,
        ], fn ($value) => $value !== null));
    }

    /**
     * Delete a phonebook.
     */
    public function deletePhonebook(string $phonebookId): Response
    {
        return $this->delete("phonebooks/{$phonebookId}");
    }

    /*
    |--------------------------------------------------------------------------
    | Contacts
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve all contacts in a phonebook.
     */
    public function contacts(string $phonebookId): Response
    {
        return $this->get("phonebooks/{$phonebookId}/contacts");
    }

    /**
     * Add a single contact to a phonebook.
     */
    public function addContact(
        string $phonebookId,
        string $phoneNumber,
        ?string $countryCode = null,
        ?string $emailAddress = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null
    ): Response {
        return $this->post("phonebooks/{$phonebookId}/contacts", array_filter([
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
            'email_address' => $emailAddress,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company' => $company,
        ], fn ($value) => $value !== null));
    }

    /**
     * Bulk-add contacts to a phonebook from a CSV file.
     *
     * By default $file is a path on the local filesystem. Pass $disk to read it
     * from a Laravel filesystem disk instead (e.g. 's3', 'local', 'public'):
     *
     *     $termii->addContactsFromFile($id, 'imports/contacts.csv', '234', 's3');
     */
    public function addContactsFromFile(string $phonebookId, string $file, string $countryCode, ?string $disk = null): Response
    {
        $contents = $disk !== null
            ? Storage::disk($disk)->get($file)
            : file_get_contents($file);

        return $this->addContactsFromContents($phonebookId, (string) $contents, basename($file), $countryCode);
    }

    /**
     * Bulk-add contacts to a phonebook from raw CSV contents.
     *
     * Handy when the CSV is not on disk, e.g. an uploaded file:
     *
     *     $file = $request->file('csv');
     *     $termii->addContactsFromContents($id, $file->get(), $file->getClientOriginalName(), '234');
     */
    public function addContactsFromContents(string $phonebookId, string $contents, string $filename, string $countryCode): Response
    {
        $response = $this->client()
            ->attach('file', $contents, $filename)
            ->post($this->url('phonebooks/contacts/upload'), [
                'api_key' => $this->apiKey,
                'country_code' => $countryCode,
                'pid' => $phonebookId,
            ]);

        return $this->maybeThrow($response);
    }

    /**
     * Delete a contact from a phonebook.
     *
     * Termii's docs only document the path `phonebooks/{id}/contacts` for this
     * action without specifying how the contact is identified, so the contact
     * id is sent in the request body.
     */
    public function deleteContact(string $phonebookId, string $contactId): Response
    {
        return $this->delete("phonebooks/{$phonebookId}/contacts", [], ['id' => $contactId]);
    }

    /*
    |--------------------------------------------------------------------------
    | Campaigns
    |--------------------------------------------------------------------------
    */

    /**
     * Send a campaign to every contact in a phonebook.
     *
     * @param  array<string, mixed>  $options  Extra fields such as delimiter,
     *                                         remove_duplicate, campaign_type,
     *                                         schedule_time, schedule_sms_status
     *                                         and enable_link_tracking.
     */
    public function sendCampaign(
        string $countryCode,
        string $senderId,
        string $message,
        string $phonebookId,
        string $channel = 'generic',
        string $messageType = 'plain',
        array $options = []
    ): Response {
        return $this->post('sms/campaigns/send', array_merge([
            'country_code' => $countryCode,
            'sender_id' => $senderId,
            'message' => $message,
            'phonebook_id' => $phonebookId,
            'channel' => $channel,
            'message_type' => $messageType,
        ], $options));
    }

    /**
     * Retrieve all campaigns on your account.
     */
    public function campaigns(): Response
    {
        return $this->get('sms/campaigns');
    }

    /**
     * Retrieve the history/details of a single campaign.
     */
    public function campaignHistory(string $campaignId): Response
    {
        return $this->get("sms/campaigns/{$campaignId}");
    }

    /**
     * Retry a campaign.
     */
    public function retryCampaign(string $campaignId): Response
    {
        return $this->patch("sms/campaigns/{$campaignId}");
    }
}
