<?php

namespace DealNews\InngestApi;

use DealNews\InngestApi\Resource\V1\CancellationsResource;
use DealNews\InngestApi\Resource\V1\EventsResource;
use DealNews\InngestApi\Resource\V1\RunsResource;
use DealNews\InngestApi\Resource\V1\SignalsResource;
use DealNews\InngestApi\Resource\V1\WebhooksResource;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

/**
 * Entry point for the legacy Inngest REST API v1 client. v1 is a
 * separate, unrelated API surface from v2 (different resource groups,
 * response shapes, and path structure — v1 paths already include their
 * own `/v1` prefix), so it gets its own facade rather than being folded
 * into DealNews\InngestApi\Client.
 */
class V1Client {

    protected HttpClient $http;

    protected ?CancellationsResource $cancellations = null;
    protected ?EventsResource $events               = null;
    protected ?RunsResource $runs                   = null;
    protected ?SignalsResource $signals             = null;
    protected ?WebhooksResource $webhooks           = null;

    /**
     * @param string $api_key Bearer token: your environment's signing key
     *        (signkey-...). Unlike v2, v1 does not support authenticating
     *        with an Inngest dashboard API key.
     * @param string $base_uri Defaults to the production API. Use
     *        http://localhost:8288 for the Inngest Dev Server.
     * @param string|null $environment Default value for the X-Inngest-Env
     *        header, sent with every request unless a resource method
     *        overrides it.
     */
    public function __construct(
        string $api_key,
        string $base_uri = 'https://api.inngest.com',
        ?string $environment = null,
        ?GuzzleClientInterface $guzzle = null,
    ) {
        $this->http = new HttpClient($api_key, $base_uri, $environment, $guzzle);
    }

    public function cancellations(): CancellationsResource {
        return $this->cancellations ??= new CancellationsResource($this->http);
    }

    public function events(): EventsResource {
        return $this->events ??= new EventsResource($this->http);
    }

    public function runs(): RunsResource {
        return $this->runs ??= new RunsResource($this->http);
    }

    public function signals(): SignalsResource {
        return $this->signals ??= new SignalsResource($this->http);
    }

    public function webhooks(): WebhooksResource {
        return $this->webhooks ??= new WebhooksResource($this->http);
    }
}
