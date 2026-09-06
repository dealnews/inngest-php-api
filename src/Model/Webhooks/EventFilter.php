<?php

namespace DealNews\InngestApi\Model\Webhooks;

/**
 * Restricts which event names a webhook allows or denies.
 */
class EventFilter {

    /**
     * @param string[] $events
     */
    public function __construct(
        public readonly array $events = [],
        public readonly ?FilterType $filter = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            events: $data['events'] ?? [],
            filter: isset($data['filter']) ? FilterType::tryFrom($data['filter']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return array_filter([
            'events' => $this->events,
            'filter' => $this->filter?->value,
        ], static fn ($value) => $value !== null);
    }
}
