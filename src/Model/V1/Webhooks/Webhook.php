<?php

namespace DealNews\InngestApi\Model\V1\Webhooks;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A webhook endpoint that turns incoming HTTP requests into Inngest
 * events, as returned by the v1 API.
 */
class Webhook {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $url = null,
        public readonly ?string $transform = null,
        public readonly ?\DateTimeImmutable $created_at = null,
        public readonly ?\DateTimeImmutable $updated_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:         $data['id']        ?? null,
            name:       $data['name']      ?? null,
            url:        $data['url']       ?? null,
            transform:  $data['transform'] ?? null,
            created_at: DateTimeConverter::parse($data['created_at'] ?? null),
            updated_at: DateTimeConverter::parse($data['updated_at'] ?? null),
        );
    }
}
