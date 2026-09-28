<?php

namespace DealNews\InngestApi\Resource\V1;

use DealNews\InngestApi\Model\V1\ResponseMetadata;
use DealNews\InngestApi\Model\V1\Runs\FunctionRun;
use DealNews\InngestApi\Model\V1\Runs\Job;
use DealNews\InngestApi\Pagination\ListResult;
use DealNews\InngestApi\Resource\AbstractResource;

/**
 * Client for the v1 function run endpoints: fetching, cancelling, and
 * listing a run's queue jobs.
 */
class RunsResource extends AbstractResource {

    /**
     * Returns a single function run by its run ID.
     */
    public function get(string $run_id): FunctionRun {
        $response = $this->http->request('GET', "/v1/runs/{$run_id}");

        return FunctionRun::fromArray($response['data'] ?? []);
    }

    /**
     * Cancels a running function immediately. No new steps will run
     * after a function is cancelled.
     */
    public function cancel(string $run_id): void {
        $this->http->request('DELETE', "/v1/runs/{$run_id}");
    }

    /**
     * Fetches a subset of the function run's jobs within the function's
     * queue, in order of earliest to latest. This endpoint is rate
     * limited and cached for 5 seconds by the API.
     */
    public function jobs(string $run_id): ListResult {
        $response = $this->http->request('GET', "/v1/runs/{$run_id}/jobs");

        return new ListResult(
            items:    array_map(static fn (array $job) => Job::fromArray($job), $response['data'] ?? []),
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
