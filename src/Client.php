<?php

namespace DealNews\InngestApi;

use DealNews\InngestApi\Resource\AccountResource;
use DealNews\InngestApi\Resource\AppsResource;
use DealNews\InngestApi\Resource\EnvironmentsResource;
use DealNews\InngestApi\Resource\EventsResource;
use DealNews\InngestApi\Resource\ExperimentsResource;
use DealNews\InngestApi\Resource\FunctionsResource;
use DealNews\InngestApi\Resource\InsightsResource;
use DealNews\InngestApi\Resource\KeysResource;
use DealNews\InngestApi\Resource\PartnerAccountsResource;
use DealNews\InngestApi\Resource\RunsResource;
use DealNews\InngestApi\Resource\SandboxesResource;
use DealNews\InngestApi\Resource\SandboxProcessesResource;
use DealNews\InngestApi\Resource\SessionsResource;
use DealNews\InngestApi\Resource\WebhooksResource;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

/**
 * Entry point for the Inngest REST API v2 client.
 */
class Client {

    protected HttpClient $http;

    protected ?AccountResource $account                       = null;
    protected ?AppsResource $apps                             = null;
    protected ?EnvironmentsResource $environments             = null;
    protected ?EventsResource $events                         = null;
    protected ?ExperimentsResource $experiments               = null;
    protected ?FunctionsResource $functions                   = null;
    protected ?InsightsResource $insights                     = null;
    protected ?KeysResource $keys                             = null;
    protected ?PartnerAccountsResource $partner_accounts      = null;
    protected ?RunsResource $runs                             = null;
    protected ?SandboxesResource $sandboxes                   = null;
    protected ?SandboxProcessesResource $sandbox_processes    = null;
    protected ?SessionsResource $sessions                     = null;
    protected ?WebhooksResource $webhooks                     = null;

    /**
     * @param string $api_key Bearer token: an API key (sk-inn-api-...) or
     *        environment signing key (signkey-...).
     * @param string $base_uri Defaults to the production API. Use
     *        http://localhost:8288/api/v2 for the Inngest Dev Server.
     * @param string|null $environment Default value for the X-Inngest-Env
     *        header, sent with every request unless a resource method
     *        overrides it.
     */
    public function __construct(
        string $api_key,
        string $base_uri = 'https://api.inngest.com/v2',
        ?string $environment = null,
        ?GuzzleClientInterface $guzzle = null,
    ) {
        $this->http = new HttpClient($api_key, $base_uri, $environment, $guzzle);
    }

    public function account(): AccountResource {
        return $this->account ??= new AccountResource($this->http);
    }

    public function apps(): AppsResource {
        return $this->apps ??= new AppsResource($this->http);
    }

    public function environments(): EnvironmentsResource {
        return $this->environments ??= new EnvironmentsResource($this->http);
    }

    public function events(): EventsResource {
        return $this->events ??= new EventsResource($this->http);
    }

    public function experiments(): ExperimentsResource {
        return $this->experiments ??= new ExperimentsResource($this->http);
    }

    public function functions(): FunctionsResource {
        return $this->functions ??= new FunctionsResource($this->http);
    }

    public function insights(): InsightsResource {
        return $this->insights ??= new InsightsResource($this->http);
    }

    public function keys(): KeysResource {
        return $this->keys ??= new KeysResource($this->http);
    }

    public function partnerAccounts(): PartnerAccountsResource {
        return $this->partner_accounts ??= new PartnerAccountsResource($this->http);
    }

    public function runs(): RunsResource {
        return $this->runs ??= new RunsResource($this->http);
    }

    public function sandboxes(): SandboxesResource {
        return $this->sandboxes ??= new SandboxesResource($this->http);
    }

    public function sandboxProcesses(): SandboxProcessesResource {
        return $this->sandbox_processes ??= new SandboxProcessesResource($this->http);
    }

    public function sessions(): SessionsResource {
        return $this->sessions ??= new SessionsResource($this->http);
    }

    public function webhooks(): WebhooksResource {
        return $this->webhooks ??= new WebhooksResource($this->http);
    }
}
