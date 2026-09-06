<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * A single row of an Insights query result. The API represents a row as a
 * plain list of values, positionally aligned with the query result's
 * column metadata, rather than a name-keyed map -- this is a thin wrapper
 * around that raw value list.
 */
class Row {

    /**
     * @param array<int, mixed> $values
     */
    public function __construct(
        public readonly array $values = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            values: $data['values'] ?? [],
        );
    }
}
