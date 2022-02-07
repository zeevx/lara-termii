<?php

namespace Zeevx\LaraTermii;

use Illuminate\Support\Facades\Http;

class LaraTermii
{
    /*
     *
     * Private Props
     */
    private string $key;

    /**
     * LaraTermii constructor.
     */
    public function __construct(string $api_key)
    {
        $this->key = $api_key;
    }

    private function base($url): string
    {
        return "https://api.ng.termii.com/{$url}";
    }


    private function checkStatus(int $status): \Illuminate\Http\JsonResponse
    {
        if ($status) {
            if ($status === 200)
                return response()->json([
                    'success' => true,
                    'message' => 'OK: Request was successful.'
                ]);
            elseif ($status === 400)
                return response()->json([
                    'success' => false,
                    'message' => 'Bad Request: Indicates that the server cannot or will not process the request due to something that is perceived to be a client error'
                ]);
            elseif ($status === 401)
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: No valid API key provided'
                ]);
            elseif ($status === 403)
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: The API key doesn\'t have permissions to perform the request.'
                ]);
            elseif ($status === 404)
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found: The requested resource doesn\'t exist.'
                ]);
            elseif ($status === 405)
                return response()->json([
                    'success' => false,
                    'message' => 'Method Not allowed: The selected http method is not allowed'
                ]);
            elseif ($status === 422)
                return response()->json([
                    'success' => false,
                    'message' => 'Unprocessable entity: indicates that the server understands the content type of the request entity, and the syntax of the request entity is correct, but it was unable to process the contained instructions'
                ]);
            elseif ($status === 429)
                return response()->json([
                    'success' => false,
                    'message' => 'Too Many Requests: Indicates the user has sent too many requests in a given amount of time'
                ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Server Errors: Something went wrong on Termii\'s end OR status was not returned'
        ]);
    }

    /*
	* Method name: balance.
	* Description: returns your total balance and balance information from your wallet, such as currency.
	*/
    public function balance(): string
    {
        $request = Http::get($this->base("get-balance?api_key={$this->key}"));
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    /*
     * Method name: getBalance.
     * Description: returns your sent messages.
     */
    public function history(): string
    {
        $request = Http::get($this->base("sms/inbox?api_key={$this->key}"));
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    public function historyStatus()
    {
        //Will be added later
    }

    /*
   * Method name: status.
   * Description: allows businesses verify phone numbers and automatically detect their status as well as current network.
   * params: phone_number and country_code.
   */
    public function status(int $phone_number, string $country_code): string
    {
        $request = Http::get($this->base("insight/number/query?api_key={$this->key}&phone_number={$phone_number}&country_code={$country_code}"));
        $status = $request->status();
        //There is a fix here
        //TODO: Fix
        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    /*
	* Method name: search.
	* Description: returns the status of a phone number.
	* params: phone_number.
	*/
    public function search(int $phone_number): string
    {
        $request = Http::get($this->base("check/dnd?api_key={$this->key}&phone_number={$phone_number}"));
        $status = $request->status();
        //There is a fix here
        //TODO: Fix
        if (json_decode($this->checkStatus($status)->content())->success || $status === 404) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }


    /*
	* Method name: allSenderId.
	* Description: Fetch all Sender Ids.
	*/
    public function allSenderId(): string
    {
        $request = Http::get($this->base("sender-id?api_key={$this->key}"));
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    public function senderIdStatus()
    {
        //Will be added later
    }


    /*
	* Method name: submitSenderId.
	* Description: Submit Sender ID.
	* params: sender_id, use_case, company.
	*/
    public function submitSenderId(string $sender_id, string $use_case, string $company): string
    {
        $request = Http::post($this->base("sender-id/request"), [
            "api_key" => $this->key,
            "sender_id" => $sender_id,
            "usecase" => $use_case,
            "company" => $company
        ]);
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success || $status === 404) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }



    /*
	* Method name: sendMessage.
	* Description: Send Message.
	* params: to, from, sms, channel, media, media_url, media_caption.
	*/
    public function sendMessage(int $to, string $from, string $sms, string $channel = "generic", bool $media = false, string $media_url = null, string $media_caption = null): string
    {
        $type = "plain";

        if ($media == true && $channel == "whatsapp") {
            $channel = "whatsapp";

            $data = [
                "api_key" => $this->key,
                "to" => $to,
                "from" => $from,
                "type" => $type,
                "channel" => $channel,
                "media" => json_encode([
                    "media.url" => $media_url,
                    "media.caption" => $media_caption
                ])
            ];
        }

        $data = [
            "api_key" => $this->key,
            "to" => $to,
            "from" => $from,
            "sms" => $sms,
            "type" => $type,
            "channel" => $channel
        ];

        $request = Http::post($this->base("sms/send"), $data);
        $status = $request->status();
        //There is a fix here
        //TODO: Fix
        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }



    /*
	* Method name: sendOTP.
	* Description: Send OTP.
	* params: to, from, message_type, pin_attempts, pin_time_to_live, pin_length, pin_placeholder,message_text, channel.
	*/
    public function sendOTP(int $to, string $from, string $message_type, int $pin_attempts, int $pin_time_to_live, int $pin_length, string $pin_placeholder, string $message_text, string $channel = "generic"): string
    {
        $data = [
            "api_key" => $this->key,
            "to" => $to,
            "from" => $from,
            "message_type" => $message_type,
            "channel" => $channel,
            "pin_attempts" => $pin_attempts,
            "pin_time_to_live" => $pin_time_to_live,
            "pin_length" => $pin_length,
            "pin_placeholder" => $pin_placeholder,
            "message_text" => $message_text
        ];

        $request = Http::post($this->base("sms/otp/send"), $data);
        $status = $request->status();

        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }



    /*
   * Method name: sendVoiceOTP.
   * Description: Send Voice OTP.
   * params: to, from, pin_attempts, pin_time_to_live, pin_length.
   */
    public function sendVoiceOTP(int $to, int $pin_attempts, int $pin_time_to_live, int $pin_length): string
    {
        $data = [
            "api_key" => $this->key,
            "phone_number" => $to,
            "pin_attempts" => $pin_attempts,
            "pin_time_to_live" => $pin_time_to_live,
            "pin_length" => $pin_length
        ];

        $request = Http::post($this->base("sms/otp/send/voice"), $data);
        $status = $request->status();

        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }



    /*
   * Method name: Voice Call.
   * Description: Send voice call.
   * params: to, code.
   */
    public function sendVoiceCall(int $to, int $code): string
    {
        $data = [
            "api_key" => $this->key,
            "phone_number" => $to,
            "code" => $code,
        ];

        $request = Http::post($this->base("sms/otp/call"), $data);
        $status = $request->status();

        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    public function verifyOTP(string $pinId, string $pin): string
    {
        $data = [
            "api_key" => $this->key,
            "pin_id" => $pinId,
            "pin" => $pin,
        ];

        $request = Http::post($this->base("sms/otp/verify"), $data);
        $status = $request->status();

        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();

    }

    /*
	* Method name: sendInAppOTP.
	* Description: Send In-App OTP.
	* params: to, from, message_type, pin_attempts, pin_time_to_live, pin_length, pin_placeholder,message_text, channel.
	*/
    public function sendInAppOTP(int $to, int $pin_attempts, int $pin_time_to_live, int $pin_length, string $pin_type): string
    {

        $data = [
            "api_key" => $this->key,
            "phone_number" => $to,
            "pin_type" => $pin_type,
            "pin_attempts" => $pin_attempts,
            "pin_time_to_live" => $pin_time_to_live,
            "pin_length" => $pin_length
        ];

        $request = Http::post($this->base("sms/otp/generate"), $data);
        $status = $request->status();

        if (json_decode($this->checkStatus($status)->content())->success || $status === 400) {
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }


}
