<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * Describes the shape of a specific event type's data, as nested JSON
 * schema.
 */
class EventSchema {

    /**
     * @param array<string, mixed>|null $schema
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?array $schema = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            name:   $data['name']   ?? null,
            schema: $data['schema'] ?? null,
        );
    }
}
