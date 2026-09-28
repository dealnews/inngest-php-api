<?php

namespace DealNews\InngestApi\Model\V1\Events;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * An event and metadata attached to the event, as returned by the v1 API.
 */
class Event {

    /**
     * @param array<string, mixed>|null $data
     * @param array<string, mixed>|null $user
     */
    public function __construct(
        public readonly ?string $internal_id = null,
        public readonly ?string $account_id = null,
        public readonly ?string $environment_id = null,
        public readonly ?string $source = null,
        public readonly ?string $source_id = null,
        public readonly ?\DateTimeImmutable $received_at = null,
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?array $data = null,
        public readonly ?array $user = null,
        public readonly ?int $ts = null,
        public readonly ?string $v = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            internal_id:    $data['internal_id']   ?? null,
            account_id:     $data['accountID']     ?? null,
            environment_id: $data['environmentID'] ?? null,
            source:         $data['source']        ?? null,
            source_id:      $data['sourceID']      ?? null,
            received_at:    DateTimeConverter::parse($data['receivedAt'] ?? null),
            id:             $data['id']             ?? null,
            name:           $data['name']           ?? null,
            data:           $data['data']           ?? null,
            user:           $data['user']           ?? null,
            ts:             $data['ts']             ?? null,
            v:              $data['v']              ?? null,
        );
    }
}
