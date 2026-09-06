<?php

namespace DealNews\InngestApi\Model\Events;

/**
 * The result of sending a single event.
 */
class SentEvent {

    public function __construct(
        public readonly ?string $event_id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            event_id: $data['eventId'] ?? null,
        );
    }
}
