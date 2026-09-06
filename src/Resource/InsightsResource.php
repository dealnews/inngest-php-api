<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Insights\EventSchema;
use DealNews\InngestApi\Model\Insights\InsightsTable;
use DealNews\InngestApi\Model\Insights\QueryPromptResult;
use DealNews\InngestApi\Model\Insights\QueryResult;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /insights endpoints: a SQL-like analytics query
 * interface over Inngest event and function run data.
 */
class InsightsResource extends AbstractResource {

    /**
     * Lists event type schemas, where each schema describes the shape of
     * a specific event's data as nested JSON.
     */
    public function listEventSchemas(?string $cursor = null, int $limit = 20): PaginatedResult {
        $response = $this->http->request('GET', '/insights/events/schemas', query: [
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return $this->toPaginatedResult($response, static fn (array $schema) => EventSchema::fromArray($schema));
    }

    /**
     * Executes an Insights query, written in modified ClickHouse SQL,
     * against event and function run data.
     */
    public function query(string $query): QueryResult {
        $response = $this->http->request('POST', '/insights/query', json: [
            'query' => $query,
        ]);

        return QueryResult::fromArray($response['data'] ?? []);
    }

    /**
     * Translates a natural language prompt into an Insights SQL query.
     */
    public function generateQueryFromPrompt(string $prompt): QueryPromptResult {
        $response = $this->http->request('POST', '/insights/query/prompt', json: [
            'prompt' => $prompt,
        ]);

        return QueryPromptResult::fromArray($response['data'] ?? []);
    }

    /**
     * Lists the tables, and their columns, that are available for
     * querying via the Insights query endpoint.
     */
    public function listTables(): PaginatedResult {
        $response = $this->http->request('GET', '/insights/tables');

        return $this->toPaginatedResult($response, static fn (array $table) => InsightsTable::fromArray($table));
    }

    /**
     * Maps a `data`/`page`/`metadata` response envelope into a
     * PaginatedResult, applying the given mapper to each item in `data`.
     *
     * @param array<string, mixed> $response
     */
    protected function toPaginatedResult(array $response, callable $mapper): PaginatedResult {
        return new PaginatedResult(
            items:    array_map($mapper, $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
