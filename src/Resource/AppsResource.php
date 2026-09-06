<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Apps\App;
use DealNews\InngestApi\Model\Apps\SyncAppResult;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /apps endpoints.
 */
class AppsResource extends AbstractResource {

    /**
     * Lists active apps in the authenticated environment, or archived apps
     * when `$archived` is true.
     */
    public function list(?string $cursor = null, int $limit = 20, bool $archived = false): PaginatedResult {
        $response = $this->http->request('GET', '/apps', query: [
            'cursor'   => $cursor,
            'limit'    => $limit,
            'archived' => $archived,
        ]);

        return new PaginatedResult(
            items:    array_map(static fn (array $app) => App::fromArray($app), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Fetches details for a single app, including sync metadata and
     * function count.
     */
    public function get(string $app_id): App {
        $response = $this->http->request('GET', "/apps/{$app_id}");

        return App::fromArray($response['data'] ?? []);
    }

    /**
     * Syncs an app at the given URL. A 422 response from the API is not
     * treated as an error here: the sync is still persisted, and its
     * failure is recorded on the returned result's `error` property.
     */
    public function sync(string $app_id, string $url): SyncAppResult {
        $response = $this->http->request(
            'POST',
            "/apps/{$app_id}/syncs",
            json: ['url' => $url],
            extra_success_statuses: [422],
        );

        return SyncAppResult::fromArray($response['data'] ?? []);
    }
}
