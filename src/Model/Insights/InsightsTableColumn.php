<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * A single column available on an Insights table, as reported by the
 * `/insights/tables` endpoint.
 */
class InsightsTableColumn {

    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            name:        $data['name']        ?? null,
            description: $data['description'] ?? null,
            type:        $data['type']        ?? null,
        );
    }
}
