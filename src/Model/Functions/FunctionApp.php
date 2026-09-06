<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * A lightweight reference to the app a function definition belongs to.
 */
class FunctionApp {

    public function __construct(
        public readonly ?string $id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id: $data['id'] ?? null,
        );
    }
}
