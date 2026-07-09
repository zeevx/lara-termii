<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Zeevx\LaraTermii\Concerns\Engage\HandlesOtp;
use Zeevx\LaraTermii\Exceptions\TermiiException;
use Zeevx\LaraTermii\Concerns\Engage\SendsMessages;
use Zeevx\LaraTermii\Concerns\Engage\ManagesAccount;
use Zeevx\LaraTermii\Concerns\Engage\ManagesContacts;
use Zeevx\LaraTermii\Concerns\Engage\ManagesCampaigns;
use Zeevx\LaraTermii\Concerns\Engage\ManagesSenderIds;
use Zeevx\LaraTermii\Concerns\Engage\ManagesPhonebooks;

class LaraTermii
{
    use HandlesOtp;
    use ManagesAccount;
    use ManagesCampaigns;
    use ManagesContacts;
    use ManagesPhonebooks;
    use ManagesSenderIds;
    use SendsMessages;

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

    /**
     * Lazily created eSIM sub-client.
     */
    protected ?LaraTermiiEsim $esim = null;

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

        $this->baseUrl = rtrim($baseUrl ?? (string) config('termii.base_url', 'https://v4.api.termii.com'), '/');
        $this->senderId = $senderId ?? config('termii.sender_id');
        $this->channel = $channel ?? (string) config('termii.channel', 'generic');
        $this->timeout = $timeout ?? (int) config('termii.timeout', 30);
        $this->throw = $throw ?? (bool) config('termii.throw', false);
    }

    /**
     * The Sotel eSIM sub-client. It shares this instance's credentials and
     * handles its own bearer-token authentication.
     */
    public function esim(): LaraTermiiEsim
    {
        return $this->esim ??= new LaraTermiiEsim($this->apiKey, $this->baseUrl, $this->timeout, $this->throw);
    }

    /**
     * Build a configured HTTP client.
     */
    protected function client(): PendingRequest
    {
        return Http::timeout($this->timeout)->acceptJson();
    }

    /**
     * Build a fully qualified endpoint URL.
     */
    protected function url(string $path): string
    {
        return $this->baseUrl.'/api/'.ltrim($path, '/');
    }

    /**
     * Optionally, throw on a failed (4xx/5xx) response.
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
}
