<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Keys\EventKey;
use DealNews\InngestApi\Model\Keys\SigningKey;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /keys endpoints.
 */
class KeysResource extends AbstractResource {

    /**
     * Lists event keys for the account, optionally scoped to an
     * environment. Defaults to the production environment's keys.
     */
    public function listEventKeys(?string $cursor = null, int $limit = 20, ?string $environment = null): PaginatedResult {
        $response = $this->http->request(
            'GET',
            '/keys/events',
            query:   ['cursor' => $cursor, 'limit' => $limit],
            headers: $this->environmentHeader($environment),
        );

        return new PaginatedResult(
            items:    array_map(static fn (array $key) => EventKey::fromArray($key), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Lists signing keys for the account, optionally scoped to an
     * environment. Defaults to the production environment's keys.
     */
    public function listSigningKeys(?string $cursor = null, int $limit = 20, ?string $environment = null): PaginatedResult {
        $response = $this->http->request(
            'GET',
            '/keys/signing',
            query:   ['cursor' => $cursor, 'limit' => $limit],
            headers: $this->environmentHeader($environment),
        );

        return new PaginatedResult(
            items:    array_map(static fn (array $key) => SigningKey::fromArray($key), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function environmentHeader(?string $environment): array {
        return $environment !== null ? ['X-Inngest-Env' => $environment] : [];
    }
}
