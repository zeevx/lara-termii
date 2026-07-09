<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;

trait ManagesCampaigns
{
    /**
     * Send a campaign to every contact in a phonebook.
     *
     * @param  string  $campaignType  "regular" or "personalized".
     * @param  string  $scheduleSmsStatus  "regular" or "scheduled" (pass
     *                                     schedule_time via $options when scheduled).
     * @param  array<string, mixed>  $options  Extra fields such as delimiter,
     *                                         remove_duplicate, schedule_time
     *                                         and enable_link_tracking.
     */
    public function sendCampaign(
        string $countryCode,
        string $senderId,
        string $message,
        string $phonebookId,
        string $channel = 'generic',
        string $messageType = 'plain',
        string $campaignType = 'regular',
        string $scheduleSmsStatus = 'regular',
        array $options = []
    ): Response {
        return $this->post('sms/campaigns/send', array_merge([
            'country_code' => $countryCode,
            'sender_id' => $senderId,
            'message' => $message,
            'phonebook_id' => $phonebookId,
            'channel' => $channel,
            'message_type' => $messageType,
            'campaign_type' => $campaignType,
            'schedule_sms_status' => $scheduleSmsStatus,
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
