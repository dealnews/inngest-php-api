# dealnews/inngest-api

A PHP client for the [Inngest](https://www.inngest.com/) REST API v2, built on Guzzle.

## Features

- Typed client methods for every v2 resource group: accounts, apps, functions, experiments, runs, environments, events, webhooks, keys, insights, sandboxes, sandbox processes, sessions, and partner accounts
- Every API response is a plain PHP value object (not a bare array), with typed, snake_case properties and `DateTimeImmutable` dates
- Fixed-value-set fields (statuses, methods, trigger types, etc.) are native PHP backed enums
- A typed exception per HTTP error class (`AuthenticationException`, `AuthorizationException`, `NotFoundException`, `ValidationException`, `RateLimitException`, `ServerException`), so you can catch what you care about
- Cursor-based pagination via a consistent `PaginatedResult` wrapper across every list endpoint
- Streaming support for sandbox log/output endpoints via PHP generators, and raw byte support for sandbox file downloads
- Works against the Inngest Dev Server as well as production, by overriding the base URI

This library only covers **REST API v2** (`https://api.inngest.com/v2`). It does not implement REST API v1 or the separate Event API used by Inngest SDKs to send events at scale.

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

## Configuration

`Client::__construct()` accepts:

| Parameter | Default | Purpose |
| --- | --- | --- |
| `$api_key` | *(required)* | Bearer token: an API key (`sk-inn-api-...`) or environment signing key (`signkey-...`) |
| `$base_uri` | `https://api.inngest.com/v2` | Override to target the Inngest Dev Server (`http://localhost:8288/api/v2`) or a self-hosted instance |
| `$environment` | `null` | Default value for the `X-Inngest-Env` header, sent on every request unless a resource method takes its own `$environment` argument (e.g. `webhooks()->list()`) |
| `$guzzle` | `null` | Inject your own `GuzzleHttp\ClientInterface` (e.g. for custom middleware, or a mock in tests); a default Guzzle client is created lazily otherwise |

## Testing

This repo ships its own test suite, split into two suites:

```bash
composer test              # unit tests only (default) — mocked, no network calls
composer test-functional   # functional tests — real calls against api.inngest.com
```

Functional tests require `tests/config.ini` (gitignored) with a real API key:

```ini
inngest.status.api_key = sk-inn-api-...
```

They only exercise read-only list/get endpoints and skip gracefully (rather than fail) when a feature isn't available for your account or the API rate-limits a call.

Other useful commands:

```bash
composer lint      # parallel-lint across src/ and tests/
composer cs-fix     # apply php-cs-fixer style fixes
composer cs-check   # check style without modifying files
```

If you're testing code that uses this library, inject a `GuzzleHttp\ClientInterface` backed by Guzzle's `MockHandler` into `Client`'s `$guzzle` parameter rather than hitting the real API — see `tests/Support/MockApi.php` in this repo for the pattern used internally.

## License

BSD 3-Clause License. See [LICENSE](LICENSE) for the full text.
