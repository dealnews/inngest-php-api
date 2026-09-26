<?php

namespace DealNews\InngestApi\Resource\V1;

use DealNews\InngestApi\Model\V1\ResponseMetadata;
use DealNews\InngestApi\Model\V1\Webhooks\Webhook;
use DealNews\InngestApi\Pagination\ListResult;
use DealNews\InngestApi\Resource\AbstractResource;

/**
 * Client for the v1 webhook endpoints.
 */
class WebhooksResource extends AbstractResource {

    /**
     * Gets all webhook endpoints in the given environment.
     */
    public function list(): ListResult {
        $response = $this->http->request('GET', '/v1/webhooks');

        return new ListResult(
            items:    array_map(static fn (array $webhook) => Webhook::fromArray($webhook), $response['data'] ?? []),
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Gets a webhook endpoint's configuration.
     */
    public function get(string $id): Webhook {
        $response = $this->http->request('GET', "/v1/webhooks/{$id}");

        return Webhook::fromArray($response['data'] ?? []);
    }

    /**
     * Creates a new webhook endpoint with the given configuration.
     */
    public function create(string $name, ?string $transform = null): Webhook {
        $response = $this->http->request('POST', '/v1/webhooks', json: array_filter([
            'name'      => $name,
            'transform' => $transform,
        ], static fn ($value) => $value !== null));

        return Webhook::fromArray($response['data'] ?? []);
    }

    /**
     * Updates a webhook endpoint's configuration.
     */
    public function update(string $id, ?string $name = null, ?string $transform = null): Webhook {
        $response = $this->http->request('PUT', "/v1/webhooks/{$id}", json: array_filter([
            'name'      => $name,
            'transform' => $transform,
        ], static fn ($value) => $value !== null));

        return Webhook::fromArray($response['data'] ?? []);
    }

    /**
     * Deletes a webhook endpoint.
     */
    public function delete(string $id): void {
        $this->http->request('DELETE', "/v1/webhooks/{$id}");
    }
}
