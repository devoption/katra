<?php

namespace App\Services\Surreal;

use RuntimeException;

class SurrealQueryException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $codeName = null,
        public readonly mixed $details = null,
        public readonly ?int $statementIndex = null,
        ?RuntimeException $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    public function isTableMissing(string $table): bool
    {
        $normalizedTable = strtolower($table);

        if (is_string($this->codeName) && strcasecmp($this->codeName, 'TABLE_NOT_FOUND') === 0) {
            return true;
        }

        if (is_string($this->details)) {
            $normalizedDetails = strtolower($this->details);

            return str_contains($normalizedDetails, $normalizedTable)
                && str_contains($normalizedDetails, 'does not exist');
        }

        return false;
    }

    public function isDuplicateRecord(): bool
    {
        if (is_string($this->codeName) && strcasecmp($this->codeName, 'DUPLICATE') === 0) {
            return true;
        }

        if (! is_string($this->details)) {
            return false;
        }

        $normalizedDetails = strtolower($this->details);

        return str_contains($normalizedDetails, 'already exists')
            || str_contains($normalizedDetails, 'duplicate');
    }
}
