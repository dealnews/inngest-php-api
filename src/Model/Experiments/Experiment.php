<?php

namespace DealNews\InngestApi\Model\Experiments;

use DealNews\InngestApi\Model\Functions\FunctionRef;
use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A summary of an observed experiment for one function.
 */
class Experiment {

    /**
     * @param string[] $variants
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?FunctionRef $function = null,
        public readonly ?string $selection_strategy = null,
        public readonly array $variants = [],
        public readonly ?int $variant_count = null,
        public readonly ?int $total_runs = null,
        public readonly ?\DateTimeImmutable $first_seen = null,
        public readonly ?\DateTimeImmutable $last_seen = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:                 $data['id'] ?? null,
            function:           isset($data['function']) ? FunctionRef::fromArray($data['function']) : null,
            selection_strategy: $data['selectionStrategy'] ?? null,
            variants:           $data['variants']          ?? [],
            variant_count:      $data['variantCount']      ?? null,
            total_runs:         $data['totalRuns']         ?? null,
            first_seen:         DateTimeConverter::parse($data['firstSeen'] ?? null),
            last_seen:          DateTimeConverter::parse($data['lastSeen']  ?? null),
        );
    }
}
