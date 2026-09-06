<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Functions\FunctionDefinition;
use DealNews\InngestApi\Model\Functions\InvokeResult;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /apps/{appId}/functions endpoints.
 */
class FunctionsResource extends AbstractResource {

    /**
     * Lists function configuration and status details for an app.
     */
    public function list(string $app_id, ?string $cursor = null, int $limit = 20): PaginatedResult {
        $response = $this->http->request('GET', "/apps/{$app_id}/functions", query: [
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return new PaginatedResult(
            items:    array_map(static fn (array $function) => FunctionDefinition::fromArray($function), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Fetches function configuration and status details for a function
     * within an app.
     */
    public function get(string $app_id, string $function_id): FunctionDefinition {
        $response = $this->http->request('GET', "/apps/{$app_id}/functions/{$function_id}");

        return FunctionDefinition::fromArray($response['data'] ?? []);
    }

    /**
     * Invokes a function, executing it either asynchronously or
     * synchronously depending on the SDK's configuration for it.
     *
     * @param array<string, mixed>|null $data
     */
    public function invoke(
        string $app_id,
        string $function_id,
        ?array $data = null,
        ?string $idempotency_key = null,
    ): InvokeResult {
        $body = array_filter([
            'data'           => $data,
            'idempotencyKey' => $idempotency_key,
        ], static fn ($value) => $value !== null);

        $response = $this->http->request('POST', "/apps/{$app_id}/functions/{$function_id}/invoke", json: $body);

        return InvokeResult::fromArray($response['data'] ?? []);
    }
}
