<?php

namespace DealNews\InngestApi\Resource\V1;

use DealNews\InngestApi\Model\V1\Cancellations\Cancellation;
use DealNews\InngestApi\Model\V1\ResponseMetadata;
use DealNews\InngestApi\Pagination\ListResult;
use DealNews\InngestApi\Resource\AbstractResource;

/**
 * Client for the v1 bulk cancellation endpoints.
 */
class CancellationsResource extends AbstractResource {

    /**
     * Returns all cancellations in your environment.
     */
    public function list(): ListResult {
        $response = $this->http->request('GET', '/v1/cancellations');

        return new ListResult(
            items:    array_map(static fn (array $cancellation) => Cancellation::fromArray($cancellation), $response['data'] ?? []),
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Creates a bulk cancellation, cancelling all functions in the given
     * time range matching an optional expression.
     */
    public function create(
        string $app_id,
        string $function_id,
        \DateTimeInterface $started_before,
        ?\DateTimeInterface $started_after = null,
        ?string $if = null,
    ): Cancellation {
        $response = $this->http->request('POST', '/v1/cancellations', json: array_filter([
            'app_id'         => $app_id,
            'function_id'    => $function_id,
            'started_before' => $started_before->format(\DateTimeInterface::RFC3339),
            'started_after'  => $started_after?->format(\DateTimeInterface::RFC3339),
            'if'             => $if,
        ], static fn ($value) => $value !== null));

        return Cancellation::fromArray($response['data'] ?? []);
    }

    /**
     * Deletes a cancellation, preventing it from stopping function runs
     * between the given dates with the given expression.
     */
    public function delete(string $id): void {
        $this->http->request('DELETE', "/v1/cancellations/{$id}");
    }
}
