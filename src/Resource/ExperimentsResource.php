<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Experiments\Experiment;
use DealNews\InngestApi\Model\Experiments\ExperimentDetail;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /experiments endpoints.
 */
class ExperimentsResource extends AbstractResource {

    /**
     * Lists observed experiments for one function in the authenticated
     * environment.
     */
    public function listForFunction(
        string $app_id,
        string $function_id,
        ?string $cursor = null,
        int $limit = 20,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
    ): PaginatedResult {
        $response = $this->http->request(
            'GET',
            "/apps/{$app_id}/functions/{$function_id}/experiments",
            query: [
                'cursor' => $cursor,
                'limit'  => $limit,
                'from'   => $from?->format(\DateTimeInterface::RFC3339),
                'until'  => $until?->format(\DateTimeInterface::RFC3339),
            ],
        );

        return $this->toPaginatedResult($response);
    }

    /**
     * Fetches variant run counts and score aggregates for one experiment
     * on one function.
     */
    public function get(
        string $app_id,
        string $function_id,
        string $experiment_id,
        ?string $variant = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
    ): ExperimentDetail {
        $response = $this->http->request(
            'GET',
            "/apps/{$app_id}/functions/{$function_id}/experiments/{$experiment_id}",
            query: [
                'variant' => $variant,
                'from'    => $from?->format(\DateTimeInterface::RFC3339),
                'until'   => $until?->format(\DateTimeInterface::RFC3339),
            ],
        );

        return ExperimentDetail::fromArray($response['data'] ?? []);
    }

    /**
     * Lists observed experiments in the authenticated environment,
     * optionally scoped to an app and/or function.
     */
    public function list(
        ?string $cursor = null,
        int $limit = 20,
        ?string $app_id = null,
        ?string $function_id = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
    ): PaginatedResult {
        $response = $this->http->request(
            'GET',
            '/experiments',
            query: [
                'cursor'     => $cursor,
                'limit'      => $limit,
                'appId'      => $app_id,
                'functionId' => $function_id,
                'from'       => $from?->format(\DateTimeInterface::RFC3339),
                'until'      => $until?->format(\DateTimeInterface::RFC3339),
            ],
        );

        return $this->toPaginatedResult($response);
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function toPaginatedResult(array $response): PaginatedResult {
        return new PaginatedResult(
            items:    array_map(static fn (array $experiment) => Experiment::fromArray($experiment), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
