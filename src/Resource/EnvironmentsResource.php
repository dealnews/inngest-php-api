<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Environments\Env;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /envs endpoints.
 */
class EnvironmentsResource extends AbstractResource {

    /**
     * Lists custom environments for the account.
     */
    public function list(?string $cursor = null, int $limit = 50): PaginatedResult {
        $response = $this->http->request('GET', '/envs', query: [
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return $this->toPaginatedResult($response);
    }

    /**
     * Creates a custom environment.
     */
    public function create(string $id, string $name): Env {
        $response = $this->http->request('POST', '/envs', json: [
            'id'   => $id,
            'name' => $name,
        ]);

        return Env::fromArray($response['data'] ?? []);
    }

    /**
     * Archives or unarchives an environment. Only the archived status can
     * be updated.
     */
    public function patch(string $id, bool $is_archived): Env {
        $response = $this->http->request('PATCH', "/envs/{$id}", json: [
            'isArchived' => $is_archived,
        ]);

        return Env::fromArray($response['data'] ?? []);
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function toPaginatedResult(array $response): PaginatedResult {
        return new PaginatedResult(
            items:    array_map(static fn (array $env) => Env::fromArray($env), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
