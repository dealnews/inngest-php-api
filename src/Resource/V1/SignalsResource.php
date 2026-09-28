<?php

namespace DealNews\InngestApi\Resource\V1;

use DealNews\InngestApi\Model\V1\Signals\ResumeSignalResult;
use DealNews\InngestApi\Resource\AbstractResource;

/**
 * Client for the v1 signals endpoint.
 */
class SignalsResource extends AbstractResource {

    /**
     * Resumes a function run that is awaiting the given signal via its
     * `step.waitForSignal` step, with the given data.
     */
    public function resume(string $signal, mixed $data = null): ResumeSignalResult {
        $response = $this->http->request('POST', '/v1/signals', json: array_filter([
            'signal' => $signal,
            'data'   => $data,
        ], static fn ($value) => $value !== null));

        return ResumeSignalResult::fromArray($response);
    }
}
