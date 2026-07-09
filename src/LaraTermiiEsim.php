<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii;

use Illuminate\Http\Client\Response;
use Zeevx\LaraTermii\Concerns\Sotel\ManagesEsims;
use Zeevx\LaraTermii\Concerns\Sotel\ManagesPlans;
use Zeevx\LaraTermii\Concerns\Sotel\AuthenticatesRequests;

class LaraTermiiEsim
{
    use AuthenticatesRequests;
    use ManagesEsims;
    use ManagesPlans;

    public function __construct(
        protected string $apiKey,
        protected string $baseUrl,
        protected int $timeout,
        protected bool $throw
    ) {}

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
     * @param  array<string, mixed>  $query
     */
    protected function get(string $path, array $query = []): Response
    {
        return $this->maybeThrow($this->client()->get($this->url($path), $query));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function post(string $path, array $payload = []): Response
    {
        return $this->maybeThrow($this->client()->post($this->url($path), $payload));
    }
}
