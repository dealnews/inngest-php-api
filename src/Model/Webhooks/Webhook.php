<?php

namespace DealNews\InngestApi\Model\Webhooks;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A webhook that turns incoming HTTP requests into Inngest events.
 */
class Webhook {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $url = null,
        public readonly ?string $environment = null,
        public readonly ?string $transform = null,
        public readonly ?string $response = null,
        public readonly ?EventFilter $event_filter = null,
        public readonly ?\DateTimeImmutable $created_at = null,
        public readonly ?\DateTimeImmutable $updated_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:           $data['id']          ?? null,
            name:         $data['name']        ?? null,
            url:          $data['url']         ?? null,
            environment:  $data['environment'] ?? null,
            transform:    $data['transform']   ?? null,
            response:     $data['response']    ?? null,
            event_filter: isset($data['eventFilter']) ? EventFilter::fromArray($data['eventFilter']) : null,
            created_at:   DateTimeConverter::parse($data['createdAt'] ?? null),
            updated_at:   DateTimeConverter::parse($data['updatedAt'] ?? null),
        );
    }
}
