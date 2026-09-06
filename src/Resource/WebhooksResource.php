<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Model\Webhooks\EventFilter;
use DealNews\InngestApi\Model\Webhooks\Webhook;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /env/webhooks endpoints. Both endpoints require an
 * environment to operate in.
 */
class WebhooksResource extends AbstractResource {

    /**
     * Lists webhooks in the given environment.
     */
    public function list(string $environment, ?string $cursor = null, int $limit = 20): PaginatedResult {
        $response = $this->http->request(
            'GET',
            '/env/webhooks',
            query:   ['cursor' => $cursor, 'limit' => $limit],
            headers: ['X-Inngest-Env' => $environment],
        );

        return new PaginatedResult(
            items:    array_map(static fn (array $webhook) => Webhook::fromArray($webhook), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Creates a webhook in the given environment.
     */
    public function create(
        string $environment,
        string $name,
        ?EventFilter $event_filter = null,
        ?string $transform = null,
        ?string $response = null,
    ): Webhook {
        $body = array_filter([
            'name'        => $name,
            'eventFilter' => $event_filter?->toArray(),
            'transform'   => $transform,
            'response'    => $response,
        ], static fn ($value) => $value !== null);

        $api_response = $this->http->request(
            'POST',
            '/env/webhooks',
            json:    $body,
            headers: ['X-Inngest-Env' => $environment],
        );

        return Webhook::fromArray($api_response['data'] ?? []);
    }
}
