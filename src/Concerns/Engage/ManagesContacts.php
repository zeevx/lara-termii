<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Engage;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;

trait ManagesContacts
{
    /**
     * Retrieve all contacts in a phonebook.
     */
    public function contacts(string $phonebookId): Response
    {
        return $this->get("phonebooks/{$phonebookId}/contacts");
    }

    /**
     * Add a single contact to a phonebook.
     */
    public function addContact(
        string $phonebookId,
        string $phoneNumber,
        ?string $countryCode = null,
        ?string $emailAddress = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null
    ): Response {
        return $this->post("phonebooks/{$phonebookId}/contacts", array_filter([
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
            'email_address' => $emailAddress,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company' => $company,
        ], fn ($value) => $value !== null));
    }

    /**
     * Bulk-add contacts to a phonebook from a CSV file.
     *
     * By default $file is a path on the local filesystem. Pass $disk to read it
     * from a Laravel filesystem disk instead (e.g. 's3', 'local', 'public'):
     *
     *     $termii->addContactsFromFile($id, 'imports/contacts.csv', '234', 's3');
     */
    public function addContactsFromFile(string $phonebookId, string $file, string $countryCode, ?string $disk = null): Response
    {
        $contents = $disk !== null
            ? Storage::disk($disk)->get($file)
            : file_get_contents($file);

        return $this->addContactsFromContents($phonebookId, (string) $contents, basename($file), $countryCode);
    }

    /**
     * Bulk-add contacts to a phonebook from raw CSV contents.
     *
     * Handy when the CSV is not on disk, e.g. an uploaded file:
     *
     *     $file = $request->file('csv');
     *     $termii->addContactsFromContents($id, $file->get(), $file->getClientOriginalName(), '234');
     */
    public function addContactsFromContents(string $phonebookId, string $contents, string $filename, string $countryCode): Response
    {
        $contact = json_encode([
            'pid' => $phonebookId,
            'country_code' => $countryCode,
            'api_key' => $this->apiKey,
        ]);

        $response = $this->client()
            ->attach('file', $contents, $filename)
            ->attach('contact', (string) $contact, null, ['Content-Type' => 'application/json'])
            ->post($this->url('phonebooks/contacts/upload'));

        return $this->maybeThrow($response);
    }

    /**
     * Delete a contact from a phonebook.
     *
     * Termii's docs only document the path `phonebooks/{id}/contacts` for this
     * action without specifying how the contact is identified, so the contact
     * id is sent in the request body.
     */
    public function deleteContact(string $phonebookId, string $contactId): Response
    {
        return $this->delete("phonebooks/{$phonebookId}/contacts", [], ['id' => $contactId]);
    }
}
