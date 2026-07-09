<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Sotel;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Zeevx\LaraTermii\Exceptions\TermiiException;

trait AuthenticatesRequests
{
    /**
     * The bearer token obtained from the authenticate endpoint.
     */
    protected ?string $token = null;

    /**
     * Exchange the API key for a bearer token (cached for this instance).
     *
     * Called automatically before the first eSIM request; call it manually to
     * refresh an expired token.
     */
    public function authenticate(): Response
    {
        $response = $this->maybeThrow(
            Http::timeout($this->timeout)
                ->acceptJson()
                ->post($this->url('esim/authenticate'), ['api_key' => $this->apiKey])
        );

        $this->token = $response->json('token') ?? $response->json('data.token');

        return $response;
    }

    /**
     * Build a client carrying the bearer token, authenticating first if needed.
     */
    protected function client(): PendingRequest
    {
        if ($this->token === null) {
            $this->authenticate();
        }

        if ($this->token === null || $this->token === '') {
            throw TermiiException::esimAuthenticationFailed();
        }

        return Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders(['X-Token' => $this->token]);
    }
}
