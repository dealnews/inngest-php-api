<?php

namespace DealNews\InngestApi\Model\Experiments;

/**
 * The traffic weight assigned to one experiment variant.
 */
class ExperimentVariantWeight {

    public function __construct(
        public readonly ?string $variant_name = null,
        public readonly ?float $weight = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            variant_name: $data['variantName'] ?? null,
            weight:       $data['weight']      ?? null,
        );
    }
}
