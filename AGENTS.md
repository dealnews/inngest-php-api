# dealnews/inngest-api

## Project Overview

A PHP client library for the **Inngest REST API v2** (`https://api.inngest.com/v2`), built on Guzzle. Requires PHP `^8.2`. This library only covers REST API v2 — not v1, and not the separate Event API — by deliberate scope decision. It covers every v2 resource group: Account, Apps, Functions, Experiments, Runs, Environments, Events, Webhooks, Keys, Insights, Sandboxes, Sandbox Processes, Sessions, and Partner Accounts.

## Key Commands

- Install: `composer install`
- Lint: `composer lint`
- Fix: `composer cs-fix`
- Test all: `composer test` (runs the `unit` suite only — mocked, no network calls)
- Test single file: `composer test -- tests/Unit/Resource/AccountResourceTest.php`
- Functional tests (hit the real API): `composer test-functional` — requires `tests/config.ini`

## Project Structure

- `src/Client.php` — entry point facade; lazily builds one resource class per API resource group (`$client->apps()`, `$client->runs()`, etc.)
- `src/HttpClient.php` — Guzzle wrapper: auth headers, query/body encoding, JSON decoding, error-to-exception mapping, streaming support
- `src/Resource/` — one class per API resource group (`AccountResource`, `AppsResource`, `RunsResource`, `SandboxesResource`, ...)
- `src/Model/<Group>/` — value objects for each resource group's request/response schemas, namespaced to match (`Model/Apps`, `Model/Runs`, `Model/Sandboxes`, ...)
- `src/Exception/` — exception hierarchy: `ApiException` base, with `AuthenticationException` (401), `AuthorizationException` (403), `NotFoundException` (404), `ValidationException` (400/409/422), `RateLimitException` (429), `ServerException` (5xx); `TransportException` for network-level failures
- `src/Pagination/PaginatedResult.php` — wraps a list response's items plus `Model\Page` and `Model\ResponseMetadata`
- `src/Support/DateTimeConverter.php` — parses API date-time strings to `DateTimeImmutable`
- `tests/Unit/` — mocked tests (Guzzle `MockHandler`), run by default
- `tests/Functional/` — tests against the real Inngest API, not run by default (see Workflow)

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
- `tests/config.ini` (gitignored, not in the repo) holds a real Inngest API key as `inngest.status.api_key = sk-inn-api-...`, used only by the functional test suite.

## Workflow

- Functional tests only exercise list/get endpoints (read-only) — never extend them to cover create/update/delete/invoke/cancel/etc. endpoints, since they run against a real account.
- Functional tests skip automatically (not fail) when `tests/config.ini` is missing, or when the API responds 401/403/404/429 for a given account/endpoint (a plan-gated feature, or no data yet to fetch) — use `FunctionalTestCase::skipIfUnavailable()` for that pattern rather than asserting exact failure behavior.
- All tests should pass at the end of code changes, with no PHPUnit deprecation warnings.
- Run `composer lint`, `composer test`, and `composer cs-fix` after code changes.

## Key Files

- `src/Client.php` — library entry point
- `src/HttpClient.php` — all HTTP/auth/error-mapping logic lives here
- `composer.json` — composer scripts are the canonical commands (`test`, `test-functional`, `lint`, `cs-fix`, `cs-check`)
- `phpunit.xml.dist` — defines the `unit` (default) and `functional` test suites
- `tests/config.ini` — gitignored, required for functional tests, not present in the repo
