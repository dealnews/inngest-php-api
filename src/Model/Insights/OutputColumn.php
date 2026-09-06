<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * Metadata describing a single column of an Insights query result.
 */
class OutputColumn {

    public function __construct(
        public readonly ?string $name = null,
        public readonly ?OutputColumnType $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            name: $data['name'] ?? null,
            type: isset($data['type']) ? OutputColumnType::tryFrom($data['type']) : null,
        );
    }
}
