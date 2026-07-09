<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;

trait ManagesAccount
{
    /**
     * Retrieve your total balance and wallet information.
     */
    public function balance(): Response
    {
        return $this->get('get-balance');
    }

    /**
     * Retrieve reports for messages sent across the sms, voice & whatsapp channels.
     *
     * Pass a message id to retrieve the report for that single message.
     */
    public function history(?string $messageId = null): Response
    {
        return $this->get('sms/inbox', array_filter([
            'message_id' => $messageId,
        ], fn ($value) => $value !== null));
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
}
