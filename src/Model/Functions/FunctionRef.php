<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * A lightweight reference to a function and its app, as returned alongside
 * observed experiments.
 */
class FunctionRef {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?AppRef $app = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:   $data['id']   ?? null,
            name: $data['name'] ?? null,
            app:  isset($data['app']) ? AppRef::fromArray($data['app']) : null,
        );
    }
}
