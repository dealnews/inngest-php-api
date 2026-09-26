<?php

namespace DealNews\InngestApi\Resource\V1;

use DealNews\InngestApi\Model\V1\Events\Event;
use DealNews\InngestApi\Model\V1\ResponseMetadata;
use DealNews\InngestApi\Model\V1\Runs\FunctionRun;
use DealNews\InngestApi\Pagination\ListResult;
use DealNews\InngestApi\Resource\AbstractResource;

/**
 * Client for the v1 event endpoints: listing, fetching a single event,
 * and listing the function runs an event triggered.
 */
class EventsResource extends AbstractResource {

    /**
     * Fetches recent events from your environment.
     */
    public function list(
        ?\DateTimeInterface $received_before = null,
        ?\DateTimeInterface $received_after = null,
        ?int $limit = null,
        ?string $name = null,
        ?string $cursor = null,
    ): ListResult {
        $response = $this->http->request('GET', '/v1/events', query: [
            'received_before' => $this->formatDateTime($received_before),
            'received_after'  => $this->formatDateTime($received_after),
            'limit'           => $limit,
            'name'            => $name,
            'cursor'          => $cursor,
        ]);

        return new ListResult(
            items:    array_map(static fn (array $event) => Event::fromArray($event), $response['data'] ?? []),
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Retrieves a single event within your environment, given its
     * internal ID.
     */
    public function get(string $internal_id): Event {
        $response = $this->http->request('GET', "/v1/events/{$internal_id}");

        return Event::fromArray($response['data'] ?? []);
    }

    /**
     * Returns function runs initialized by the given event.
     */
    public function listRuns(string $internal_id): ListResult {
        $response = $this->http->request('GET', "/v1/events/{$internal_id}/runs");

        return new ListResult(
            items:    array_map(static fn (array $run) => FunctionRun::fromArray($run), $response['data'] ?? []),
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Formats a date-time for use as an RFC 3339 query parameter value.
     */
    protected function formatDateTime(?\DateTimeInterface $date): ?string {
        return $date?->format(\DateTimeInterface::RFC3339);
    }
}
