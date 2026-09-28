<?php

namespace DealNews\InngestApi;

use DealNews\InngestApi\Exception\ApiException;
use DealNews\InngestApi\Exception\AuthenticationException;
use DealNews\InngestApi\Exception\AuthorizationException;
use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\Exception\RateLimitException;
use DealNews\InngestApi\Exception\ServerException;
use DealNews\InngestApi\Exception\TransportException;
use DealNews\InngestApi\Exception\ValidationException;
use DealNews\InngestApi\Model\ErrorDetail;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Thin wrapper around Guzzle for the Inngest REST API, shared by the v1
 * and v2 clients. Applies bearer auth and the optional environment
 * header, decodes JSON bodies, and maps error responses to typed
 * exceptions.
 */
class HttpClient {

    protected ?GuzzleClientInterface $guzzle = null;

    public function __construct(
        protected string $api_key,
        protected string $base_uri = 'https://api.inngest.com/v2',
        protected ?string $environment = null,
        ?GuzzleClientInterface $guzzle = null,
    ) {
        $this->guzzle = $guzzle;
    }

    /**
     * Sends a request and returns the decoded JSON response body.
     *
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $json
     * @param array<string, string> $headers
     * @param int[] $extra_success_statuses HTTP statuses besides the 2xx
     *        range that should be returned as-is instead of throwing.
     *
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws TransportException
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $json = null,
        array $headers = [],
        array $extra_success_statuses = [],
    ): array {
        $headers += $this->defaultHeaders();

        $options = [
            'headers'     => $headers,
            'query'       => $this->buildQuery($query),
            'http_errors' => false,
        ];

        if ($json !== null) {
            // An empty PHP array encodes as a JSON array ([]), but these
            // endpoints expect an empty JSON object ({}) for an empty body.
            $options['json'] = $json === [] ? new \stdClass() : $json;
        }

        $response = $this->send($method, $path, $options);
        $status   = $response->getStatusCode();
        $data     = $this->decode($response);

        if ($status >= 400 && !in_array($status, $extra_success_statuses, true)) {
            throw $this->exceptionFor($status, $data);
        }

        return $data;
    }

    /**
     * Sends a request expecting a raw, non-JSON response body (e.g. a
     * binary file download). Applies the same auth headers and error
     * mapping as request(), but returns the body as-is instead of
     * attempting to JSON-decode it.
     *
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     *
     * @return array{body: string, content_type: ?string}
     *
     * @throws ApiException
     * @throws TransportException
     */
    public function requestRaw(
        string $method,
        string $path,
        array $query = [],
        array $headers = [],
    ): array {
        $headers += $this->defaultHeaders();

        $options = [
            'headers'     => $headers,
            'query'       => $this->buildQuery($query),
            'http_errors' => false,
        ];

        $response = $this->send($method, $path, $options);
        $status   = $response->getStatusCode();

        if ($status >= 400) {
            throw $this->exceptionFor($status, $this->decode($response));
        }

        return [
            'body'         => (string) $response->getBody(),
            'content_type' => $response->getHeaderLine('Content-Type') ?: null,
        ];
    }

    /**
     * Sends a request expecting a newline-delimited-JSON streamed response
     * and yields each decoded line as it arrives, without buffering the
     * whole body in memory. Applies the same auth headers and error
     * mapping as request(), throwing before any streaming is attempted if
     * the response status is not successful.
     *
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     *
     * @return \Generator<array<string, mixed>>
     *
     * @throws ApiException
     * @throws TransportException
     */
    public function stream(
        string $method,
        string $path,
        array $query = [],
        array $headers = [],
    ): \Generator {
        $headers += $this->defaultHeaders();

        $options = [
            'headers'     => $headers,
            'query'       => $this->buildQuery($query),
            'http_errors' => false,
            'stream'      => true,
        ];

        $response = $this->send($method, $path, $options);
        $status   = $response->getStatusCode();

        if ($status >= 400) {
            throw $this->exceptionFor($status, $this->decode($response));
        }

        yield from $this->readLines($response->getBody());
    }

    /**
     * Reads a PSR-7 stream incrementally, yielding each complete,
     * newline-delimited JSON-decoded line as it becomes available.
     */
    protected function readLines(\Psr\Http\Message\StreamInterface $body): \Generator {
        $buffer = '';

        while (!$body->eof()) {
            $buffer .= $body->read(8192);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line   = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if ($line !== '') {
                    $decoded = json_decode($line, true);

                    if (is_array($decoded)) {
                        yield $decoded;
                    }
                }
            }
        }

        $remainder = trim($buffer);

        if ($remainder !== '') {
            $decoded = json_decode($remainder, true);

            if (is_array($decoded)) {
                yield $decoded;
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function send(string $method, string $path, array $options): \Psr\Http\Message\ResponseInterface {
        try {
            $response = $this->guzzle()->request($method, $this->url($path), $options);
        } catch (GuzzleException $e) {
            throw new TransportException('Request to the Inngest API failed: ' . $e->getMessage(), 0, $e);
        }

        return $response;
    }

    protected function url(string $path): string {
        return rtrim($this->base_uri, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Builds a query string, skipping null values, encoding booleans as
     * the literal strings `true`/`false`, and encoding array values as
     * repeated `key=value` pairs (e.g. `status=A&status=B`) rather than
     * PHP's bracketed `status[0]=A&status[1]=B` — these are RPC-style
     * repeated query parameters, not PHP-style array params.
     *
     * @param array<string, mixed> $query
     */
    protected function buildQuery(array $query): string {
        $pairs = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            foreach (is_array($value) ? $value : [$value] as $item) {
                if ($item === null) {
                    continue;
                }

                $pairs[] = rawurlencode((string) $key) . '=' . rawurlencode($this->queryScalar($item));
            }
        }

        return implode('&', $pairs);
    }

    protected function queryScalar(mixed $value): string {
        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array {
        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
        ];

        if (!empty($this->environment)) {
            $headers['X-Inngest-Env'] = $this->environment;
        }

        return $headers;
    }

    /**
     * @return array<string, mixed>
     */
    protected function decode(\Psr\Http\Message\ResponseInterface $response): array {
        $body   = (string) $response->getBody();
        $return = [];

        if ($body !== '') {
            $decoded = json_decode($body, true);
            $return  = is_array($decoded) ? $decoded : [];
        }

        return $return;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function exceptionFor(int $status, array $data): ApiException {
        $errors = array_map(
            static fn (array $error) => ErrorDetail::fromArray($error),
            $data['errors'] ?? [],
        );

        // v2 errors are an `errors` array; v1 errors are a single `error`
        // string instead, so fall back to that shape when present.
        if ($errors === [] && is_string($data['error'] ?? null)) {
            $errors = [ErrorDetail::fromArray(['message' => $data['error']])];
        }

        $message = $errors[0]->message ?? "Inngest API request failed with status {$status}";

        return match (true) {
            $status === 401                               => new AuthenticationException($message, $status, $errors, $data),
            $status === 403                               => new AuthorizationException($message, $status, $errors, $data),
            $status === 404                               => new NotFoundException($message, $status, $errors, $data),
            $status === 429                               => new RateLimitException($message, $status, $errors, $data),
            in_array($status, [400, 409, 422], true)      => new ValidationException($message, $status, $errors, $data),
            $status >= 500                                => new ServerException($message, $status, $errors, $data),
            default                                       => new ApiException($message, $status, $errors, $data),
        };
    }

    protected function guzzle(): GuzzleClientInterface {
        return $this->guzzle ??= new GuzzleClient();
    }
}
