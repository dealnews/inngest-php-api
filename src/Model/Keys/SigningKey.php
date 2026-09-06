<?php

namespace DealNews\InngestApi\Model\Keys;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A signing key, used to authenticate requests to the REST API.
 */
class SigningKey {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $key = null,
        public readonly ?string $environment = null,
        public readonly ?\DateTimeImmutable $created_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:          $data['id']          ?? null,
            name:        $data['name']        ?? null,
            key:         $data['key']         ?? null,
            environment: $data['environment'] ?? null,
            created_at:  DateTimeConverter::parse($data['createdAt'] ?? null),
        );
    }
}
