<?php

namespace DealNews\InngestApi\Model\Experiments;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * Variant run counts and score aggregates for one experiment on one
 * function.
 */
class ExperimentDetail {

    /**
     * @param ExperimentVariantWeight[] $variant_weights
     * @param ExperimentVariantMetrics[] $variants
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $selection_strategy = null,
        public readonly array $variant_weights = [],
        public readonly array $variants = [],
        public readonly ?\DateTimeImmutable $first_seen = null,
        public readonly ?\DateTimeImmutable $last_seen = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:                 $data['id']                ?? null,
            selection_strategy: $data['selectionStrategy'] ?? null,
            variant_weights:    array_map(
                static fn (array $weight) => ExperimentVariantWeight::fromArray($weight),
                $data['variantWeights'] ?? [],
            ),
            variants: array_map(
                static fn (array $variant) => ExperimentVariantMetrics::fromArray($variant),
                $data['variants'] ?? [],
            ),
            first_seen: DateTimeConverter::parse($data['firstSeen'] ?? null),
            last_seen:  DateTimeConverter::parse($data['lastSeen']  ?? null),
        );
    }
}
