# dealnews/inngest-api

A PHP client for the [Inngest](https://www.inngest.com/) REST API, built on Guzzle. Covers both the current **v2** API and the legacy **v1** API, via two separate client classes.

## Features

- Typed client methods for every v2 resource group: accounts, apps, functions, experiments, runs, environments, events, webhooks, keys, insights, sandboxes, sandbox processes, sessions, and partner accounts
- Typed client methods for every v1 resource group: events, function runs, signals, bulk cancellations, and webhooks
- Every API response is a plain PHP value object (not a bare array), with typed, snake_case properties and `DateTimeImmutable` dates
- Fixed-value-set fields (statuses, methods, trigger types, etc.) are native PHP backed enums
- A typed exception per HTTP error class (`AuthenticationException`, `AuthorizationException`, `NotFoundException`, `ValidationException`, `RateLimitException`, `ServerException`), so you can catch what you care about
- Cursor-based pagination via a consistent `PaginatedResult` wrapper across every v2 list endpoint; v1 list endpoints (which don't return a cursor) use the simpler `ListResult` wrapper
- Streaming support for sandbox log/output endpoints via PHP generators, and raw byte support for sandbox file downloads
- Works against the Inngest Dev Server as well as production, by overriding the base URI

`DealNews\InngestApi\Client` covers **REST API v2** (`https://api.inngest.com/v2`) and `DealNews\InngestApi\V1Client` covers the legacy **REST API v1** (`https://api.inngest.com`). Neither covers the separate Event API used by Inngest SDKs to send events at scale.

> **Note:** REST API v2 is still in active development, and Inngest hasn't enabled every v2 endpoint for every account yet. If a call that looks correct still fails with a 401/403/404, don't assume this library is broken — contact Inngest support and ask them to enable that endpoint for your account.

## Installation

```bash
composer require dealnews/inngest-api
```

Requires PHP `^8.2` and `guzzlehttp/guzzle` `^7.15 || ^8.0` (installed automatically as a dependency).

## Quick Start

```php
use DealNews\InngestApi\Client;

$client = new Client($api_key); // an API key (sk-inn-api-...) or environment signing key (signkey-...)

$account = $client->account()->get();

echo $account->name;
```

Point the client at the [Inngest Dev Server](https://www.inngest.com/docs/local-development) instead of production, and/or pin a default environment (sent as the `X-Inngest-Env` header on every request):

```php
$client = new Client(
    api_key: 'dev-key',
    base_uri: 'http://localhost:8288/api/v2',
    environment: 'branch-feature-x',
);
```

### Using the v1 client

The legacy v1 API only authenticates with an environment's signing key (`signkey-...`), not a dashboard API key, and lives under `DealNews\InngestApi\V1Client`:

```php
use DealNews\InngestApi\V1Client;

$v1 = new V1Client($signing_key);

$events = $v1->events()->list(limit: 10)->items;
$runs   = $v1->events()->listRuns($events[0]->internal_id)->items;

if ($runs !== []) {
    $run = $v1->runs()->get($runs[0]->run_id);
}
```

Point it at the Dev Server the same way (no `/api/v2`-style path segment for v1 — its paths already include their own `/v1` prefix):

```php
$v1 = new V1Client(api_key: 'dev-key', base_uri: 'http://localhost:8288');
```

## Usage Examples

### Listing and paginating

Every list method returns a `PaginatedResult` with `items`, `page` (cursor info), and `metadata`:

```php
$result = $client->apps()->list(limit: 20);

foreach ($result->items as $app) {
    echo "{$app->id}: {$app->name}\n";
}

if ($result->page->has_more) {
    $next = $client->apps()->list(cursor: $result->page->cursor, limit: 20);
}
```

### Fetching a single resource

```php
$app = $client->apps()->get('my-app');
$function = $client->functions()->get('my-app', 'my-function');
```

### Syncing an app

```php
$sync = $client->apps()->sync('my-app', 'https://example.com/api/inngest');
```

### Invoking a function directly

```php
$result = $client->functions()->invoke(
    app_id: 'my-app',
    function_id: 'my-function',
    data: ['message' => 'Hello, World!'],
);

echo $result->run_id;
```

### Sending a test event

`events()->send()` uses REST API rate limits and is meant for testing/debugging, not high-volume ingestion — use an Inngest SDK or the dedicated Event API for that.

```php
$sent = $client->events()->send('user.signup', data: ['userId' => '123']);

echo $sent->event_id;
```

### Working with runs

```php
$runs = $client->runs()->list(limit: 10, status: ['FAILED']);

foreach ($runs->items as $run) {
    echo "{$run->id}: {$run->status->value}\n";
}

$run = $client->runs()->get($runs->items[0]->id);
$trace = $client->runs()->getTrace($run->id);

$client->runs()->cancel($run->id);
$client->runs()->rerun($run->id, step_id: 'step-1', input: [['foo' => 'bar']]);
```

### Sandboxes: exec, files, and streaming logs

```php
$sandbox = $client->sandboxes()->create('build-sandbox', vcpu: 1, memory_mb: 1024);

$result = $client->sandboxes()->exec($sandbox->id, ['echo', 'hello']);
echo $result->stdout;

$client->sandboxes()->writeFile($sandbox->id, 'file contents', path: '/tmp/out.txt');
$file = $client->sandboxes()->readFile($sandbox->id, '/tmp/out.txt');

foreach ($client->sandboxProcesses()->streamLogs($sandbox->id, follow: true) as $chunk) {
    echo $chunk->data; // raw bytes, decoded from the wire
}
```

### Handling errors

```php
use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\Exception\RateLimitException;
use DealNews\InngestApi\Exception\ValidationException;

try {
    $client->apps()->get('does-not-exist');
} catch (NotFoundException $e) {
    // 404
} catch (ValidationException $e) {
    // 400 / 409 / 422 — inspect $e->getErrors() for API-provided error codes/messages
} catch (RateLimitException $e) {
    // 429
}
```

A 401/403/404 on a v2 call that otherwise looks correct may mean the endpoint just isn't enabled for your account yet — see the note near the top of this README — rather than a bug in your request.

## API Documentation

`DealNews\InngestApi\Client` is the entry point. It lazily builds and returns one resource client per API resource group; each call returns the same instance:

| Method | Resource class | Covers |
| --- | --- | --- |
| `account()` | `AccountResource` | `GET /account` |
| `apps()` | `AppsResource` | list/get apps, sync an app |
| `functions()` | `FunctionsResource` | list/get functions, invoke a function |
| `experiments()` | `ExperimentsResource` | list/get function experiments |
| `runs()` | `RunsResource` | list/get runs, cancel, rerun, score, trace |
| `environments()` | `EnvironmentsResource` | list/create/patch custom environments |
| `events()` | `EventsResource` | send a single test event |
| `webhooks()` | `WebhooksResource` | list/create webhooks for an environment |
| `keys()` | `KeysResource` | list event keys and signing keys |
| `insights()` | `InsightsResource` | list event schemas/tables, run/generate insights queries |
| `sandboxes()` | `SandboxesResource` | list/create/get/destroy sandboxes, exec, read/write files |
| `sandboxProcesses()` | `SandboxProcessesResource` | list/start/get processes, output, signal, wait, stream logs |
| `sessions()` | `SessionsResource` | list session keys, sessions, and session runs |
| `partnerAccounts()` | `PartnerAccountsResource` | list/create partner sub-accounts (requires partner access) |

Every resource method:

- Accepts typed, named-friendly scalar/array parameters — no request DTOs to build up front
- Returns either a single value object (e.g. `App`, `FunctionRun`, `Sandbox`) or a `PaginatedResult` for list endpoints
- Throws a subclass of `DealNews\InngestApi\Exception\ApiException` on any 4xx/5xx response, or `TransportException` on a network-level failure

Response models mirror the API's schemas: nested objects (e.g. `FunctionRun::$app`, `FunctionRun::$trigger`) are themselves value objects, and fixed-value-set fields (e.g. `FunctionRun::$status`) are backed enums — compare with `===` against the enum's cases (e.g. `FunctionRunStatus::Completed`).

Read the source under `src/Resource/` and `src/Model/` for the full method and property list per resource group — every method has a short docblock describing what it does and what it returns.

`DealNews\InngestApi\V1Client` is the entry point for the legacy v1 API:

| Method | Resource class | Covers |
| --- | --- | --- |
| `events()` | `Resource\V1\EventsResource` | list/get events, list an event's function runs |
| `runs()` | `Resource\V1\RunsResource` | get/cancel a function run, list its queue jobs |
| `signals()` | `Resource\V1\SignalsResource` | resume a run awaiting `step.waitForSignal` |
| `cancellations()` | `Resource\V1\CancellationsResource` | list/create/delete bulk cancellations |
| `webhooks()` | `Resource\V1\WebhooksResource` | list/get/create/update/delete webhooks |

v1 list methods return a `ListResult` (`items` plus `metadata`) rather than v2's cursor-paginated `PaginatedResult`, since v1 list endpoints don't return a pagination cursor.

## Configuration

`Client::__construct()` (v2) accepts:

| Parameter | Default | Purpose |
| --- | --- | --- |
| `$api_key` | *(required)* | Bearer token: an API key (`sk-inn-api-...`) or environment signing key (`signkey-...`) |
| `$base_uri` | `https://api.inngest.com/v2` | Override to target the Inngest Dev Server (`http://localhost:8288/api/v2`) or a self-hosted instance |
| `$environment` | `null` | Default value for the `X-Inngest-Env` header, sent on every request unless a resource method takes its own `$environment` argument (e.g. `webhooks()->list()`) |
| `$guzzle` | `null` | Inject your own `GuzzleHttp\ClientInterface` (e.g. for custom middleware, or a mock in tests); a default Guzzle client is created lazily otherwise |

`V1Client::__construct()` (v1) accepts the same four parameters, except `$api_key` must be an environment signing key (`signkey-...`) — v1 does not accept a dashboard API key — and `$base_uri` defaults to `https://api.inngest.com` (Dev Server: `http://localhost:8288`).

## Testing

This repo ships its own test suite, split into two suites:

```bash
composer test              # unit tests only (default) — mocked, no network calls
composer test-functional   # functional tests — real calls against api.inngest.com
```

Functional tests require `tests/config.ini` (gitignored) with a real API key and/or signing key:

```ini
inngest.status.api_key = sk-inn-api-...
inngest.status.signing_key = signkey-...
```

`inngest.status.api_key` drives the v2 functional suite; `inngest.status.signing_key` drives the v1 one (v1 only accepts a signing key). Either can be omitted — its suite skips gracefully instead of failing. Both suites only exercise read-only list/get endpoints and skip gracefully (rather than fail) when a feature isn't available for your account or the API rate-limits a call.

Other useful commands:

```bash
composer lint      # parallel-lint across src/ and tests/
composer cs-fix     # apply php-cs-fixer style fixes
composer cs-check   # check style without modifying files
```

If you're testing code that uses this library, inject a `GuzzleHttp\ClientInterface` backed by Guzzle's `MockHandler` into `Client`'s `$guzzle` parameter rather than hitting the real API — see `tests/Support/MockApi.php` in this repo for the pattern used internally.

## License

BSD 3-Clause License. See [LICENSE](LICENSE) for the full text.
