<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Exceptions;

use InvalidArgumentException;

class TermiiException extends InvalidArgumentException
{
    public static function missingApiKey(): self
    {
        return new self(
            'No Termii API key provided. Set TERMII_API_KEY in your .env file, '
            .'publish the "termii" config, or pass the key to the LaraTermii constructor.'
        );
    }

    public static function missingSenderId(): self
    {
        return new self(
            'No Termii Sender ID provided. Set TERMII_SENDER_ID in your .env file '
            .'or pass the "from" argument explicitly.'
        );
    }
}
