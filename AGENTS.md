# dealnews/inngest-api

## Project Overview

A PHP client library for the Inngest REST API, built on Guzzle. Requires PHP `^8.2`. It covers both current API versions via two separate entry-point classes, but not the separate Event API:

- **v2** (`https://api.inngest.com/v2`, `src/Client.php`): every v2 resource group — Account, Apps, Functions, Experiments, Runs, Environments, Events, Webhooks, Keys, Insights, Sandboxes, Sandbox Processes, Sessions, and Partner Accounts.
- **v1** (`https://api.inngest.com`, `src/V1Client.php`): the legacy, still-supported v1 resource groups — Events, Function Runs, Signals, Cancellations, and Webhooks. v1 is a separate, unrelated API surface (different resource shapes, signing-key-only auth, and v1 paths already carry their own `/v1` prefix), so it isn't merged into `Client`.

## Key Commands

- Install: `composer install`
- Lint: `composer lint`
- Fix: `composer cs-fix`
- Test all: `composer test` (runs the `unit` suite only — mocked, no network calls)
- Test single file: `composer test -- tests/Unit/Resource/AccountResourceTest.php`
- Functional tests (hit the real API): `composer test-functional` — requires `tests/config.ini`

## Project Structure

- `src/Client.php` — v2 entry point facade; lazily builds one resource class per v2 resource group (`$client->apps()`, `$client->runs()`, etc.)
- `src/V1Client.php` — v1 entry point facade; lazily builds one resource class per v1 resource group (`$client->events()`, `$client->cancellations()`, etc.), sharing the same `HttpClient`/exception plumbing as v2
- `src/HttpClient.php` — Guzzle wrapper shared by both clients: auth headers, query/body encoding, JSON decoding, error-to-exception mapping (both v2's `{errors: [...]}` shape and v1's `{error: "..."}` shape), streaming support
- `src/Resource/` — one class per v2 resource group (`AccountResource`, `AppsResource`, `RunsResource`, `SandboxesResource`, ...)
- `src/Resource/V1/` — one class per v1 resource group (`EventsResource`, `RunsResource`, `SignalsResource`, `CancellationsResource`, `WebhooksResource`) — extends the same `Resource\AbstractResource` base as v2
- `src/Model/<Group>/` — value objects for each v2 resource group's request/response schemas, namespaced to match (`Model/Apps`, `Model/Runs`, `Model/Sandboxes`, ...)
- `src/Model/V1/<Group>/` — value objects for each v1 resource group's request/response schemas (`Model/V1/Events`, `Model/V1/Runs`, ...); despite the shared group names, these are unrelated to the v2 `Model/<Group>` classes and never reused across versions
- `src/Exception/` — exception hierarchy: `ApiException` base, with `AuthenticationException` (401), `AuthorizationException` (403), `NotFoundException` (404), `ValidationException` (400/409/422), `RateLimitException` (429), `ServerException` (5xx); `TransportException` for network-level failures; shared by both v1 and v2
- `src/Pagination/PaginatedResult.php` — v2 only: wraps a list response's items plus `Model\Page` (cursor) and `Model\ResponseMetadata`
- `src/Pagination/ListResult.php` — v1 only: wraps a list response's items plus `Model\V1\ResponseMetadata`; v1 list endpoints don't return a pagination cursor
- `src/Support/DateTimeConverter.php` — parses API date-time strings to `DateTimeImmutable`; shared by both versions
- `tests/Unit/` — mocked tests (Guzzle `MockHandler`), run by default; v1 resource tests live under `tests/Unit/Resource/V1/`
- `tests/Functional/` — tests against the real Inngest v2 API, not run by default (see Workflow)
- `tests/Functional/V1/` — tests against the real Inngest v1 API, not run by default (see Workflow)

## Code Style

- 1TBS bracing style
- snake_case variables
- Protected visibility by default
- Single return point preference
- Class-based API (no bare functions)
- Dependency injection is handled by optional parameters passed to class constructors (unless specified, otherwise)
- Complete PHPDoc coverage

## Non-Obvious Patterns

- Every response model is a plain value object: `public readonly` snake_case properties and a `public static function fromArray(array $data): self` factory that maps the API's camelCase JSON keys. Fixed-value-set fields (statuses, types) are native PHP backed string enums, converted via `EnumClass::tryFrom(...)`.
- `HttpClient::request()`'s query-string building is custom, not Guzzle's default: array values are sent as repeated `key=value&key=value2` pairs (not PHP's bracketed `key[0]=`), and booleans are sent as the literal strings `true`/`false` (not PHP's `1`/empty-string). Both match what the Inngest API (grpc-gateway backed) actually expects — don't "simplify" this back to passing arrays straight through to Guzzle.
- An empty array passed as `$json` to `HttpClient::request()` is encoded as `{}`, not `[]` — some POST endpoints (e.g. cancel run) have an empty request body schema, and PHP's `json_encode([])` produces a JSON array, which the API rejects.
- `HttpClient::requestRaw()` and `HttpClient::stream()` exist for the two sandbox endpoints that don't return normal JSON: raw file downloads and newline-delimited-JSON log/output streaming.
- v1 authenticates with an environment's signing key (`signkey-...`) only — unlike v2, it does not accept a dashboard API key. `V1Client`'s `$base_uri` default (`https://api.inngest.com`) has no version segment, because v1's own paths already start with `/v1/...` (v2's paths, by contrast, omit `/v2` since `Client`'s base URI already includes it).
- `tests/config.ini` (gitignored, not in the repo) holds real Inngest credentials: `inngest.status.api_key = sk-inn-api-...` for the v2 functional suite, `inngest.status.signing_key = signkey-...` for the v1 one. Either key can be absent — only that suite skips.

## Workflow

- Functional tests only exercise list/get endpoints (read-only) — never extend them to cover create/update/delete/invoke/cancel/etc. endpoints, since they run against a real account.
- Functional tests skip automatically (not fail) when `tests/config.ini` is missing or missing the relevant key, or when the API responds 401/403/404/429 for a given account/endpoint (a plan-gated feature, or no data yet to fetch) — use `FunctionalTestCase::skipIfUnavailable()` (v2) or `V1FunctionalTestCase::skipIfUnavailable()` (v1) for that pattern rather than asserting exact failure behavior.
- All tests should pass at the end of code changes, with no PHPUnit deprecation warnings.
- Run `composer lint`, `composer test`, and `composer cs-fix` after code changes.

## Key Files

- `src/Client.php` — v2 library entry point
- `src/V1Client.php` — v1 library entry point
- `src/HttpClient.php` — all HTTP/auth/error-mapping logic lives here, shared by both versions
- `composer.json` — composer scripts are the canonical commands (`test`, `test-functional`, `lint`, `cs-fix`, `cs-check`)
- `phpunit.xml.dist` — defines the `unit` (default) and `functional` test suites
- `tests/config.ini` — gitignored, required for functional tests, not present in the repo
