<?php

namespace DealNews\InngestApi\Model\Environments;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A custom environment (e.g. a branch environment) within an account.
 */
class Env {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?EnvType $type = null,
        public readonly bool $is_archived = false,
        public readonly ?\DateTimeImmutable $created_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:          $data['id']   ?? null,
            name:        $data['name'] ?? null,
            type:        isset($data['type']) ? EnvType::tryFrom($data['type']) : null,
            is_archived: $data['isArchived'] ?? false,
            created_at:  DateTimeConverter::parse($data['createdAt'] ?? null),
        );
    }
}
