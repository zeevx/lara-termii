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
    private string $base;

    /**
     * LaraTermii constructor.
     */
    public function __construct(string $api_key)
    {
        $this->key = $api_key;
        $this->base = "https://termii.com/api/";
    }


    private function checkStatus(int $status): \Illuminate\Http\JsonResponse
    {
        if ($status){
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
     * Get Balance
     */
    public function balance()
    {
        $request = Http::get("{$this->base}get-balance?api_key={$this->key}");
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success){
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    /*
     * View History of messages sent
     */
    public function history()
    {
        $request = Http::get("{$this->base}sms/inbox?api_key={$this->key}");
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success){
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    public function historyStatus()
    {
        //Will be added later
    }

    /*
     * Check Status of a number
     */
    public function status(int $phone_number, string $country_code)
    {
        $request = Http::get("{$this->base}insight/number/query?api_key={$this->key}&phone_number={$phone_number}&country_code={$country_code}");
        $status = $request->status();
        //There is a fix here
        //TODO: Fix
        if (json_decode($this->checkStatus($status)->content())->success || $status === 400){
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    /*
     * Search/Check info of a number
     */
    public function search(int $phone_number)
    {
        $request = Http::get("{$this->base}check/dnd?api_key={$this->key}&phone_number={$phone_number}");
        $status = $request->status();
        //There is a fix here
        //TODO: Fix
        if (json_decode($this->checkStatus($status)->content())->success || $status === 404){
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }


    /*
     * Fetch all Sender Ids
     */
    public function allSenderId()
    {
        $request = Http::get("{$this->base}sender-id?api_key={$this->key}");
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success){
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }

    public function senderIdStatus()
    {
        //Will be added later
    }

    /*
     * Submit Sender ID
     */
    public function submitSenderId(string $sender_id, string $use_case, string $company)
    {
        $request = Http::post("{$this->base}sender-id/request", [
            "api_key" => $this->key,
            "sender_id" => $sender_id,
            "usecase" => $use_case,
            "company" => $company
        ]);
        $status = $request->status();
        if (json_decode($this->checkStatus($status)->content())->success || $status === 404){
            return $request->getBody()->getContents();
        }
        return $this->checkStatus($status)->content();
    }


}
