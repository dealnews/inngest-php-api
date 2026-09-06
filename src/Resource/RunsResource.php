<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Model\Runs\CancelResult;
use DealNews\InngestApi\Model\Runs\FunctionRun;
use DealNews\InngestApi\Model\Runs\FunctionTrace;
use DealNews\InngestApi\Model\Runs\RerunResult;
use DealNews\InngestApi\Model\Runs\Score;
use DealNews\InngestApi\Model\Runs\ScoreInput;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the function run endpoints: listing/inspection,
 * cancellation, rerun, scoring, and tracing.
 */
class RunsResource extends AbstractResource {

    /**
     * Lists runs for a single function within an app.
     *
     * @param string[]|null $status Statuses to include, e.g. COMPLETED,
     *        FAILED, RUNNING, QUEUED, CANCELLED
     */
    public function listByFunction(
        string $app_id,
        string $function_id,
        ?string $cursor = null,
        int $limit = 20,
        ?bool $include_output = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
        ?string $time_field = null,
        ?array $status = null,
        ?bool $is_deferred = null,
        ?string $order = null,
    ): PaginatedResult {
        $response = $this->http->request('GET', "/apps/{$app_id}/functions/{$function_id}/runs", query: [
            'includeOutput' => $include_output,
            'cursor'        => $cursor,
            'limit'         => $limit,
            'from'          => $this->formatDateTime($from),
            'until'         => $this->formatDateTime($until),
            'timeField'     => $time_field,
            'status'        => $status,
            'isDeferred'    => $is_deferred,
            'order'         => $order,
        ]);

        return $this->toPaginatedRuns($response);
    }

    /**
     * Lists function runs triggered by a specific event.
     */
    public function listByEvent(
        string $event_id,
        ?string $cursor = null,
        int $limit = 20,
        ?bool $include_output = null,
    ): PaginatedResult {
        $response = $this->http->request('GET', "/events/{$event_id}/runs", query: [
            'includeOutput' => $include_output,
            'cursor'        => $cursor,
            'limit'         => $limit,
        ]);

        return $this->toPaginatedRuns($response);
    }

    /**
     * Lists runs in the authenticated environment, optionally filtered by
     * app and function IDs.
     *
     * @param string[]|null $status Statuses to include, e.g. COMPLETED,
     *        FAILED, RUNNING, QUEUED, CANCELLED
     * @param string[]|null $app_id App IDs to include
     * @param string[]|null $function_id Function IDs to include
     */
    public function list(
        ?string $cursor = null,
        int $limit = 20,
        ?bool $include_output = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
        ?string $time_field = null,
        ?array $status = null,
        ?array $app_id = null,
        ?array $function_id = null,
        ?bool $is_deferred = null,
        ?string $order = null,
    ): PaginatedResult {
        $response = $this->http->request('GET', '/runs', query: [
            'includeOutput' => $include_output,
            'cursor'        => $cursor,
            'limit'         => $limit,
            'from'          => $this->formatDateTime($from),
            'until'         => $this->formatDateTime($until),
            'timeField'     => $time_field,
            'status'        => $status,
            'appId'         => $app_id,
            'functionId'    => $function_id,
            'isDeferred'    => $is_deferred,
            'order'         => $order,
        ]);

        return $this->toPaginatedRuns($response);
    }

    /**
     * Fetches the canonical run summary for a single function run.
     */
    public function get(string $run_id, ?bool $include_output = null): FunctionRun {
        $response = $this->http->request('GET', "/runs/{$run_id}", query: [
            'includeOutput' => $include_output,
        ]);

        return FunctionRun::fromArray($response['data'] ?? []);
    }

    /**
     * Cancels an in-progress function run.
     */
    public function cancel(string $run_id): CancelResult {
        $response = $this->http->request('POST', "/runs/{$run_id}/cancel", json: []);

        return CancelResult::fromArray($response['data'] ?? []);
    }

    /**
     * Creates a new run using the original run's triggering event data,
     * optionally replaying from a specific step with replacement input.
     *
     * @param array<int, array<string, mixed>>|null $input Optional
     *        replacement step input as a JSON array
     */
    public function rerun(string $run_id, ?string $step_id = null, ?array $input = null): RerunResult {
        $from_step = array_filter([
            'stepId' => $step_id,
            'input'  => $input,
        ], static fn ($value) => $value !== null);

        $response = $this->http->request(
            'POST',
            "/runs/{$run_id}/rerun",
            json: $from_step !== [] ? ['fromStep' => $from_step] : [],
        );

        return RerunResult::fromArray($response['data'] ?? []);
    }

    /**
     * Submits one or more named scores for a function run, or for
     * specific steps when step IDs are provided on the inputs. Writes
     * are applied in order and are not atomic. Maximum 100 scores per
     * call.
     *
     * @param ScoreInput[] $scores
     *
     * @return Score[]
     */
    public function createScores(string $run_id, array $scores): array {
        $response = $this->http->request(
            'POST',
            "/runs/{$run_id}/scores",
            json: array_map(static fn (ScoreInput $score) => $score->toArray(), $scores),
        );

        return array_map(static fn (array $score) => Score::fromArray($score), $response['data'] ?? []);
    }

    /**
     * Fetches the trace tree for a single function run.
     */
    public function getTrace(string $run_id, ?bool $include_output = null): FunctionTrace {
        $response = $this->http->request('GET', "/runs/{$run_id}/trace", query: [
            'includeOutput' => $include_output,
        ]);

        return FunctionTrace::fromArray($response['data'] ?? []);
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function toPaginatedRuns(array $response): PaginatedResult {
        return new PaginatedResult(
            items:    array_map(static fn (array $run) => FunctionRun::fromArray($run), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Formats a date-time for use as an RFC 3339 query parameter value.
     */
    protected function formatDateTime(?\DateTimeInterface $date): ?string {
        return $date?->format(\DateTimeInterface::RFC3339);
    }
}
