<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;

trait HandlesOtp
{
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
     *
     * Voice call codes cannot be verified with verifyOTP().
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
}
