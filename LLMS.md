# LLMS.md — dealnews/inngest-api

Reference for an AI coding agent generating code that uses this library. This library is `DealNews\InngestApi`, a PHP client for the **Inngest REST API v2** (`https://api.inngest.com/v2`), built on Guzzle. Requires PHP `^8.2`.

**Scope**: only REST API v2 is implemented. There is no support in this library for REST API v1 or the separate Inngest Event API (the high-volume event-ingestion endpoint used by Inngest SDKs, `https://inn.gs/e/...`). If asked to send events at scale or use v1-only endpoints, say so — don't invent methods that don't exist here.

## Installation

```bash
composer require dealnews/inngest-api
```

## Bootstrapping

```php
use DealNews\InngestApi\Client;

$client = new Client(string $api_key, string $base_uri = 'https://api.inngest.com/v2', ?string $environment = null, ?GuzzleHttp\ClientInterface $guzzle = null);
```

- `$api_key` — an API key (`sk-inn-api-...`) or environment signing key (`signkey-...`), sent as `Authorization: Bearer <key>`.
- `$base_uri` — override to `http://localhost:8288/api/v2` to target the Inngest Dev Server instead of production.
- `$environment` — default value for the `X-Inngest-Env` header, sent on every request unless a resource method has its own `$environment` parameter (only `webhooks()->list()`/`create()` and `keys()->listEventKeys()`/`listSigningKeys()` do).
- `$guzzle` — inject a `GuzzleHttp\ClientInterface` (e.g. one backed by `MockHandler` for tests). If omitted, a default Guzzle client is created lazily on first request.

`$client` is cheap to construct and its resource getters (`$client->apps()`, `$client->runs()`, etc.) are memoized — call them as many times as you like, they return the same instance.

## Conventions — read before writing code

1. **Every resource getter returns a resource client object.** There is no method on `Client` itself besides the constructor and the 14 resource getters listed below.
2. **List methods return `DealNews\InngestApi\Pagination\PaginatedResult`**, not a bare array:
   ```php
   final class PaginatedResult {
       public readonly array $items;                    // array of the resource's model class
       public readonly ?DealNews\InngestApi\Model\Page $page;               // cursor, has_more, limit
       public readonly ?DealNews\InngestApi\Model\ResponseMetadata $metadata; // fetched_at, cached_until, time_range
   }
   ```
   Paginate by re-calling the same method with `cursor: $result->page->cursor` while `$result->page->has_more` is true.
3. **Every non-list method returns a typed value object** (e.g. `App`, `FunctionRun`, `Sandbox`) — never a raw array. All model properties are `public readonly` and **snake_case** (`created_at`, `function_count`, `duration_ms`), even though the wire format is camelCase.
4. **Fixed-value-set fields are native PHP backed enums** (e.g. `FunctionRunStatus`, `SandboxStatus`). Compare with `===` against enum cases (`FunctionRunStatus::Completed`), not string literals. Every enum has a matching `Unspecified`/`Unknown`-style default case for `UNSPECIFIED` values from the API — comparing against a real case name still works.
5. **Dates are `\DateTimeImmutable`**, already parsed — never format/parse date strings yourself when reading a model.
6. **Model namespaces are generally per-resource-group and not shared**, even when two groups have identically-named types. `DealNews\InngestApi\Model\Runs\FunctionRef` is not the same class as `DealNews\InngestApi\Model\Functions\FunctionRef` or `DealNews\InngestApi\Model\Sessions\FunctionRef` — these resource groups' models are self-contained. Always import the type from the same `Model\<Group>` namespace as the resource method you called. The one exception: `PartnerAccountsResource::list()` returns `Model\Account\Account` items (the same class `AccountResource::get()` returns), not a `Model\PartnerAccounts`-namespaced type.
7. **Resource methods take plain scalar/array/enum parameters, not request DTOs** — build nothing before calling a method; pass named arguments directly (e.g. `$client->apps()->list(limit: 5, archived: true)`).
8. **Errors are exceptions, not error return values.** Every 4xx/5xx response throws a subclass of `DealNews\InngestApi\Exception\ApiException`; a network-level failure (DNS, connection refused, timeout) throws `DealNews\InngestApi\Exception\TransportException`. There is no method that returns `null`/`false` on failure — wrap calls in `try`/`catch`, don't check a return value for falsiness.
9. **Sending an event through this library (`events()->send()`) is rate-limited and meant for testing/debugging**, per Inngest's own API description — don't use it for bulk/production event ingestion; that's out of scope for this library entirely (see Scope above).

## Error handling

```php
use DealNews\InngestApi\Exception\ApiException;          // base class — catch this to handle "any API error"
use DealNews\InngestApi\Exception\AuthenticationException; // 401
use DealNews\InngestApi\Exception\AuthorizationException;  // 403
use DealNews\InngestApi\Exception\NotFoundException;       // 404
use DealNews\InngestApi\Exception\ValidationException;     // 400 / 409 / 422
use DealNews\InngestApi\Exception\RateLimitException;      // 429
use DealNews\InngestApi\Exception\ServerException;         // 5xx
use DealNews\InngestApi\Exception\TransportException;      // network-level failure, not an HTTP response at all

try {
    $client->apps()->get('does-not-exist');
} catch (NotFoundException $e) {
    // ...
} catch (ApiException $e) {
    $e->getStatusCode();  // int
    $e->getErrors();      // DealNews\InngestApi\Model\ErrorDetail[] — each has ->code and ->message
    $e->getRawBody();     // array<string, mixed> — the decoded JSON error response
}
```

One deliberate exception to "errors always throw": `AppsResource::sync()` treats HTTP 422 as a *successful* response (Inngest documents this status for that endpoint as "sync completed but the app itself failed to sync") — it returns a `SyncAppResult` with `->error` populated instead of throwing. Every other endpoint throws normally on 422.

## Resource reference

### `$client->account()` → `AccountResource`
```php
get(): Account
```
`Account`: `id`, `name`, `email`, `created_at`, `updated_at`.

### `$client->apps()` → `AppsResource`
```php
list(?string $cursor = null, int $limit = 20, bool $archived = false): PaginatedResult   // items: App[]
get(string $app_id): App
sync(string $app_id, string $url): SyncAppResult
```
`App`: `id`, `name`, `app_version`, `method` (`AppMethod`), `function_count`, `is_archived`, `latest_sync` (`AppSync`), `created_at`, `archived_at`.
`AppSync`: `app_version`, `error`, `framework`, `sdk_language`, `sdk_version`, `status` (`SyncStatus`), `synced_at`, `url`.
`SyncAppResult`: `id`, `app_id`, `status` (`SyncStatus`), `error` (`SyncError` — has `code`, `message`).

### `$client->functions()` → `FunctionsResource`
```php
list(string $app_id, ?string $cursor = null, int $limit = 20): PaginatedResult   // items: FunctionDefinition[]
get(string $app_id, string $function_id): FunctionDefinition
invoke(string $app_id, string $function_id, ?array $data = null, ?string $idempotency_key = null): InvokeResult
```
`FunctionDefinition` (note: named `FunctionDefinition`, not `Function` — `Function` is a reserved word in PHP): `id`, `slug`, `name`, `app` (`FunctionApp`, just `id`), `is_archived`, `is_paused`, `triggers` (`FunctionTrigger[]`), `configuration` (`FunctionConfiguration`), `failure_handler` (`FunctionFailureHandler`).
`FunctionTrigger`: `type` (`FunctionTriggerType`), `value`, `if`.
`FunctionConfiguration`: `cancellations` (`FunctionCancellationConfiguration[]`), `concurrency` (`FunctionConcurrencyConfiguration[]`), `debounce`, `events_batch`, `priority`, `rate_limit`, `retries` (`FunctionRetryConfiguration`), `singleton` (`FunctionSingletonConfiguration`), `throttle` — see `src/Model/Functions/` for each nested config class's own fields if you need them.
`InvokeResult`: `run_id`, `queued_at`, `started_at`, `completed_at`, `result` (string — JSON, only for completed synchronous invocations), `error`.

### `$client->experiments()` → `ExperimentsResource`
```php
listForFunction(string $app_id, string $function_id, ?string $cursor = null, int $limit = 20, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null): PaginatedResult   // items: Experiment[]
get(string $app_id, string $function_id, string $experiment_id, ?string $variant = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null): ExperimentDetail
list(?string $cursor = null, int $limit = 20, ?string $app_id = null, ?string $function_id = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null): PaginatedResult   // items: Experiment[]
```
`Experiment`: `id`, `function` (`FunctionRef`), `selection_strategy`, `variants` (array), `variant_count`, `total_runs`, `first_seen`, `last_seen`.
`ExperimentDetail`: `id`, `selection_strategy`, `variant_weights` (`ExperimentVariantWeight[]`), `variants` (`ExperimentVariantMetrics[]`), `first_seen`, `last_seen`.
`ExperimentVariantMetrics`: `variant_name`, `run_count`, `metrics` (`ExperimentVariantMetric[]` — each has `key`, `min`, `max`, `avg`).

### `$client->runs()` → `RunsResource`
```php
listByFunction(string $app_id, string $function_id, ?string $cursor = null, int $limit = 20, ?bool $include_output = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null, ?string $time_field = null, ?array $status = null, ?bool $is_deferred = null, ?string $order = null): PaginatedResult   // items: FunctionRun[]
listByEvent(string $event_id, ?string $cursor = null, int $limit = 20, ?bool $include_output = null): PaginatedResult   // items: FunctionRun[]
list(?string $cursor = null, int $limit = 20, ?bool $include_output = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null, ?string $time_field = null, ?array $status = null, ?array $app_id = null, ?array $function_id = null, ?bool $is_deferred = null, ?string $order = null): PaginatedResult   // items: FunctionRun[]
get(string $run_id, ?bool $include_output = null): FunctionRun
cancel(string $run_id): CancelResult
rerun(string $run_id, ?string $step_id = null, ?array $input = null): RerunResult
createScores(string $run_id, array $scores): array   // $scores: ScoreInput[] in, Score[] out
getTrace(string $run_id, ?bool $include_output = null): FunctionTrace
```
- `$status` is an array of status strings, e.g. `['COMPLETED', 'FAILED']` — matches `FunctionRunStatus` case values. `$order` is `'ASC'` or `'DESC'`.
- `FunctionRun`: `id`, `app` (`AppRef`, just `id`), `function` (`FunctionRef`), `status` (`FunctionRunStatus`), `trigger` (`RunTrigger`), `duration_ms` (string, not int — large numbers stay as strings to avoid precision loss), `output` (array|null), `queued_at`, `started_at`, `ended_at`.
- `RunTrigger`: `event_name`, `event_ids` (string[]), `batch_id`, `is_batch`, `cron_schedule`.
- `FunctionTrace`: `run_id`, `root_span` (`TraceSpan`). `TraceSpan` is recursive: `id`, `name`, `step_id`, `step_op` (`TraceStepOp`), `status` (`TraceSpanStatus`), `duration_ms`, `input`, `output`, `queued_at`, `started_at`, `ended_at`, `metadata` (`TraceSpanMetadata[]`), `children` (`TraceSpan[]`).
- `Score`/`ScoreInput`: `name`, `value` (mixed — a finite number or boolean, per the API), `step_id`, `experiment` (`ScoreExperiment`: `id`, `variant`). `ScoreInput` additionally has no `run_id` (it's passed as the method's own `$run_id` argument); `Score` (the response) has `run_id`.

### `$client->environments()` → `EnvironmentsResource`
```php
list(?string $cursor = null, int $limit = 50): PaginatedResult   // items: Env[]
create(string $id, string $name): Env
patch(string $id, bool $is_archived): Env   // only the archived flag can be updated
```
`Env`: `id`, `name`, `type` (`EnvType`: `Production`/`Test`/`Branch`), `is_archived`, `created_at`.

### `$client->events()` → `EventsResource`
```php
send(string $name, array $data = [], ?string $id = null, ?int $ts = null, array $user = []): SentEvent
```
`$user` is deprecated by Inngest — put user data in `$data` instead. `SentEvent`: `event_id`. Use the returned `event_id` with `$client->runs()->listByEvent($event_id)` to find any runs it triggered.

### `$client->webhooks()` → `WebhooksResource`
```php
list(string $environment, ?string $cursor = null, int $limit = 20): PaginatedResult   // items: Webhook[]
create(string $environment, string $name, ?EventFilter $event_filter = null, ?string $transform = null, ?string $response = null): Webhook
```
`$environment` is required on both (sent as `X-Inngest-Env`), unlike most other list methods. `EventFilter`: `events` (string[], globs like `'orders/*'` allowed), `filter` (`FilterType::Allow`/`FilterType::Deny`). `Webhook`: `id`, `name`, `url`, `environment`, `transform`, `response`, `event_filter` (`EventFilter`), `created_at`, `updated_at`.

### `$client->keys()` → `KeysResource`
```php
listEventKeys(?string $cursor = null, int $limit = 20, ?string $environment = null): PaginatedResult    // items: EventKey[]
listSigningKeys(?string $cursor = null, int $limit = 20, ?string $environment = null): PaginatedResult  // items: SigningKey[]
```
Both `EventKey` and `SigningKey`: `id`, `name`, `key`, `environment`, `created_at`.

### `$client->insights()` → `InsightsResource`
```php
listEventSchemas(?string $cursor = null, int $limit = 20): PaginatedResult   // items: EventSchema[]
query(string $query): QueryResult          // $query is ClickHouse-flavored SQL
generateQueryFromPrompt(string $prompt): QueryPromptResult   // natural language -> SQL, via an LLM call on Inngest's side — has real latency/cost implications, don't call in a loop
listTables(): PaginatedResult   // items: InsightsTable[]
```
`QueryResult`: `columns` (`OutputColumn[]`: `name`, `type` — `OutputColumnType`), `rows` (`Row[]` — each just `values`, a positional array aligned to `columns`, not a name-keyed map), `diagnostics` (`Diagnostic[]`: `code`, `message`, `position`, `severity` — `DiagnosticSeverity`). `QueryPromptResult`: `sql`, `summary`.

### `$client->sandboxes()` → `SandboxesResource`
```php
list(?string $cursor = null, ?int $limit = null): PaginatedResult   // items: Sandbox[]
create(string $name, ?int $vcpu = null, ?int $memory_mb = null, array $environment = []): Sandbox
get(string $sandbox_id): Sandbox
destroy(string $sandbox_id): Sandbox
exec(string $sandbox_id, array $command, ?string $cwd = null, array $environment = [], ?string $timeout = null): SandboxExecResult
readFile(string $sandbox_id, ?string $path = null): SandboxFileContent
writeFile(string $sandbox_id, string $content, ?string $path = null, ?string $mode = null, ?string $content_type = null): SandboxFileWriteResult
```
`Sandbox`: `id`, `name`, `status` (`SandboxStatus`), `resources` (`SandboxResourceSpec`: `vcpu`, `memory_mb`), `image_ref`, `vpc_id`, `error`, `created_at`, `started_at`, `ended_at`.
`exec()`/`command` is an argv-style array, e.g. `['echo', 'hello']`, not a shell string. `SandboxExecResult`: `exit_code`, `stdout`, `stderr` (both already decoded from the wire's base64 encoding — plain strings), `encoding`.
`readFile()`/`writeFile()` deal in raw file bytes as plain PHP strings — `writeFile()` base64-encodes `$content` for you before sending; `readFile()` returns `SandboxFileContent` (`content`, `content_type`) already decoded, straight off the wire (this endpoint isn't JSON at all — the library uses a dedicated raw-response path internally, nothing you need to handle).

### `$client->sandboxProcesses()` → `SandboxProcessesResource`
```php
list(string $sandbox_id, ?string $cursor = null, int $limit = 50): PaginatedResult   // items: SandboxProcess[]
start(string $sandbox_id, array $command, ?string $cwd = null, ?array $environment = null): SandboxProcess
get(string $sandbox_id, string $process_id): SandboxProcess
output(string $sandbox_id, string $process_id, ?int $tail_bytes = null): SandboxProcessOutput
streamOutput(string $sandbox_id, string $process_id, ?int $tail_bytes = null): \Generator   // yields SandboxLogChunk
signal(string $sandbox_id, string $process_id, int $signal, bool $include_children = false): void
wait(string $sandbox_id, string $process_id, ?string $timeout = null): SandboxProcess   // blocks server-side until the process exits or $timeout elapses
streamLogs(string $sandbox_id, ?bool $follow = null): \Generator   // yields SandboxLogChunk
```
`SandboxProcess`: `id`, `pid`, `command` (string[]), `state` (`SandboxProcessState`), `started_at`, `ended_at`, `exit_code`, `termination_signal`.
`streamOutput()`/`streamLogs()` return a lazy `\Generator` — iterate with `foreach`, don't call `iterator_to_array()` on `streamLogs(follow: true)` (it never terminates while following live logs). `SandboxLogChunk`: `at`, `data` (string — already decoded from the wire's base64 encoding), `encoding`, `stream` (`SandboxLogStream::Stdout`/`Stderr`).
`signal()` returns `void` — the API has nothing to report back for this call.

### `$client->sessions()` → `SessionsResource`
```php
listKeys(?string $search = null, ?string $cursor = null, int $limit = 20): PaginatedResult   // items: SessionKey[]
list(string $session_key, ?string $search = null, ?string $cursor = null, int $limit = 20, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null): PaginatedResult   // items: SessionGroup[]
listRuns(string $session_key, string $session_id, ?string $cursor = null, int $limit = 20, ?\DateTimeInterface $from = null, ?\DateTimeInterface $until = null): PaginatedResult   // items: SessionRun[]
```
Drill-down flow: `listKeys()` → pick a `SessionKey::$id`, pass as `$session_key` to `list()` → pick a `SessionGroup::$id`, pass as `$session_id` to `listRuns()`.
`SessionGroup`: `id`, `functions` (`FunctionRef[]`), `run_count`, `failed_run_count`, `failure_rate`, `last_active_at`. `SessionRun`: `id`, `event_name`, `function` (`FunctionRef`), `status` (`FunctionRunStatus`), `queued_at`, `started_at`, `ended_at`.

### `$client->partnerAccounts()` → `PartnerAccountsResource`
```php
list(?string $cursor = null, int $limit = 20): PaginatedResult   // items: DealNews\InngestApi\Model\Account\Account[]
create(string $name, string $email): NewPartnerAccount
```
Requires Inngest partner API access — expect `AuthenticationException`/`AuthorizationException` if the account doesn't have it. `NewPartnerAccount`: `id`, `name`, `email`, `api_key` (only ever returned here, at creation time), `created_at`, `updated_at`.

## Enum reference

| Enum | Namespace | Cases |
| --- | --- | --- |
| `AppMethod` | `Model\Apps` | `Unspecified` `Serve` `Connect` `Api` |
| `SyncStatus` | `Model\Apps` | `Pending` `Success` `Error` `Duplicate` |
| `EnvType` | `Model\Environments` | `Production` `Test` `Branch` |
| `FunctionConcurrencyScope` | `Model\Functions` | `Unspecified` `Account` `Environment` `Function` |
| `FunctionSingletonMode` | `Model\Functions` | `Unspecified` `Skip` `Cancel` |
| `FunctionTriggerType` | `Model\Functions` | `Unspecified` `Event` `Cron` |
| `DiagnosticSeverity` | `Model\Insights` | `Unspecified` `Error` `Warning` `Info` |
| `OutputColumnType` | `Model\Insights` | `Unspecified` `String` `Number` `Boolean` `DateTime` `Complex` |
| `FunctionRunStatus` | `Model\Runs` (and, separately, `Model\Sessions`) | `Unspecified` `Queued` `Running` `Completed` `Failed` `Cancelled` |
| `TraceSpanStatus` | `Model\Runs` | `Unknown` `Running` `Completed` `Failed` `Waiting` `Cancelled` `Skipped` |
| `TraceStepOp` | `Model\Runs` | `Unspecified` `Run` `Sleep` `WaitForEvent` `Invoke` `SendEvent` `AiGateway` `WaitForSignal` |
| `SandboxStatus` | `Model\Sandboxes` | `Unspecified` `Pending` `Starting` `Running` `Paused` `Terminating` `Terminated` `Failed` |
| `SandboxProcessState` | `Model\Sandboxes` | `Unspecified` `Starting` `Running` `Exited` `Killed` `Failed` `Lost` |
| `SandboxLogStream` | `Model\Sandboxes` | `Unspecified` `Stdout` `Stderr` |
| `FilterType` | `Model\Webhooks` | `Allow` `Deny` |

## Quick reference: full example

```php
use DealNews\InngestApi\Client;
use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\Model\Runs\FunctionRunStatus;

$client = new Client($_ENV['INNGEST_API_KEY']);

try {
    $failed = $client->runs()->list(limit: 20, status: ['FAILED']);

    foreach ($failed->items as $run) {
        if ($run->status === FunctionRunStatus::Failed) {
            $trace = $client->runs()->getTrace($run->id);
            // inspect $trace->root_span, ->children, etc.
        }
    }
} catch (NotFoundException $e) {
    // ...
}
```
