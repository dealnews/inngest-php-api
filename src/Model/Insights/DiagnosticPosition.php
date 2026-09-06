<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * The location in a query string that an Insights diagnostic applies to.
 */
class DiagnosticPosition {

    public function __construct(
        public readonly ?string $context = null,
        public readonly ?int $start = null,
        public readonly ?int $end = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            context: $data['context'] ?? null,
            start:   $data['start']   ?? null,
            end:     $data['end']     ?? null,
        );
    }
}
