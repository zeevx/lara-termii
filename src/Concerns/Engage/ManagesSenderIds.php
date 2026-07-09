<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;

trait ManagesSenderIds
{
    /**
     * Retrieve the status of all registered Sender IDs.
     *
     * Results can optionally be filtered by Sender ID name and/or status
     * ("active", "pending" or "blocked").
     */
    public function allSenderId(?string $name = null, ?string $status = null): Response
    {
        return $this->get('sender-id', array_filter([
            'name' => $name,
            'status' => $status,
        ], fn ($value) => $value !== null));
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
}
