<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;

trait ManagesPhonebooks
{
    /**
     * Retrieve all phonebooks on your account.
     */
    public function phonebooks(): Response
    {
        return $this->get('phonebooks');
    }

    /**
     * Create a new phonebook.
     */
    public function createPhonebook(string $name, ?string $description = null): Response
    {
        return $this->post('phonebooks', array_filter([
            'phonebook_name' => $name,
            'description' => $description,
        ], fn ($value) => $value !== null));
    }

    /**
     * Update an existing phonebook.
     */
    public function updatePhonebook(string $phonebookId, string $name, ?string $description = null): Response
    {
        return $this->patch("phonebooks/{$phonebookId}", array_filter([
            'phonebook_name' => $name,
            'description' => $description,
        ], fn ($value) => $value !== null));
    }

    /**
     * Delete a phonebook.
     */
    public function deletePhonebook(string $phonebookId): Response
    {
        return $this->delete("phonebooks/{$phonebookId}");
    }
}
