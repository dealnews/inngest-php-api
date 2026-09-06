<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * An Insights SQL query generated from a natural language prompt.
 */
class QueryPromptResult {

    public function __construct(
        public readonly ?string $sql = null,
        public readonly ?string $summary = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            sql:     $data['sql']     ?? null,
            summary: $data['summary'] ?? null,
        );
    }
}
