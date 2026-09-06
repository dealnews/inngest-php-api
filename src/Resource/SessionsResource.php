<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Model\Sessions\SessionGroup;
use DealNews\InngestApi\Model\Sessions\SessionKey;
use DealNews\InngestApi\Model\Sessions\SessionRun;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /sessions endpoints.
 */
class SessionsResource extends AbstractResource {

    /**
     * Lists session keys observed in the authenticated environment.
     */
    public function listKeys(?string $search = null, ?string $cursor = null, int $limit = 20): PaginatedResult {
        $response = $this->http->request('GET', '/sessions', query: [
            'search' => $search,
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return new PaginatedResult(
            items:    array_map(static fn (array $key) => SessionKey::fromArray($key), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Lists sessions (grouped by session ID) observed under a session key
     * in the authenticated environment.
     */
    public function list(
        string $session_key,
        ?string $search = null,
        ?string $cursor = null,
        int $limit = 20,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
    ): PaginatedResult {
        $response = $this->http->request('GET', "/sessions/{$session_key}", query: [
            'search' => $search,
            'cursor' => $cursor,
            'limit'  => $limit,
            'from'   => $from?->format(\DateTimeInterface::RFC3339),
            'until'  => $until?->format(\DateTimeInterface::RFC3339),
        ]);

        return new PaginatedResult(
            items:    array_map(static fn (array $group) => SessionGroup::fromArray($group), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Lists the function runs associated with a single session ID under a
     * session key in the authenticated environment.
     */
    public function listRuns(
        string $session_key,
        string $session_id,
        ?string $cursor = null,
        int $limit = 20,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
    ): PaginatedResult {
        $response = $this->http->request('GET', "/sessions/{$session_key}/{$session_id}/runs", query: [
            'cursor' => $cursor,
            'limit'  => $limit,
            'from'   => $from?->format(\DateTimeInterface::RFC3339),
            'until'  => $until?->format(\DateTimeInterface::RFC3339),
        ]);

        return new PaginatedResult(
            items:    array_map(static fn (array $run) => SessionRun::fromArray($run), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
