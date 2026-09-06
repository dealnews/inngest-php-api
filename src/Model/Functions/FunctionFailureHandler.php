<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * The function invoked to handle a failed run, if configured.
 */
class FunctionFailureHandler {

    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
        );
    }
}
