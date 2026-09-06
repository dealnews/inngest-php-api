<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * A table that can be queried via the Insights query endpoint, along with
 * the columns it exposes.
 */
class InsightsTable {

    /**
     * @param InsightsTableColumn[] $columns
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly array $columns = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            name:        $data['name']        ?? null,
            description: $data['description'] ?? null,
            columns:     array_map(
                static fn (array $column) => InsightsTableColumn::fromArray($column),
                $data['columns'] ?? [],
            ),
        );
    }
}
