<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Events\SentEvent;

/**
 * Client for the /events endpoint.
 *
 * This sends a single event through the REST API, which uses REST API
 * rate limits and is meant for testing and debugging. High-volume event
 * ingestion should go through an Inngest SDK or the dedicated Event API.
 */
class EventsResource extends AbstractResource {

    /**
     * Sends a single event.
     *
     * @param array<string, mixed> $data JSON payload for the event
     * @param array<string, mixed> $user Deprecated by Inngest; put user
     *        data in $data instead
     */
    public function send(
        string $name,
        array $data = [],
        ?string $id = null,
        ?int $ts = null,
        array $user = [],
    ): SentEvent {
        $body = array_filter([
            'name' => $name,
            'data' => $data,
            'id'   => $id,
            'ts'   => $ts,
            'user' => $user,
        ], static fn ($value) => $value !== null && $value !== []);

        $response = $this->http->request('POST', '/events', json: $body);

        return SentEvent::fromArray($response['data'] ?? []);
    }
}
