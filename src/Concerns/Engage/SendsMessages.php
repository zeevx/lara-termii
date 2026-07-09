<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;

trait SendsMessages
{
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
}
