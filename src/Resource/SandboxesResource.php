<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Model\Sandboxes\Sandbox;
use DealNews\InngestApi\Model\Sandboxes\SandboxExecResult;
use DealNews\InngestApi\Model\Sandboxes\SandboxFileContent;
use DealNews\InngestApi\Model\Sandboxes\SandboxFileWriteResult;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for sandbox lifecycle, command execution, and file read/write
 * on the /sandboxes endpoints. Process management and log streaming
 * live in a sibling resource class.
 */
class SandboxesResource extends AbstractResource {

    /**
     * Lists sandboxes for the account.
     */
    public function list(?string $cursor = null, ?int $limit = null): PaginatedResult {
        $response = $this->http->request('GET', '/sandboxes', query: [
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return $this->toPaginatedResult($response);
    }

    /**
     * Creates a new sandbox.
     *
     * @param array<string, string> $environment
     */
    public function create(
        string $name,
        ?int $vcpu = null,
        ?int $memory_mb = null,
        array $environment = [],
    ): Sandbox {
        $response = $this->http->request('POST', '/sandboxes', json: [
            'name'        => $name,
            'vcpu'        => $vcpu,
            'memoryMb'    => $memory_mb,
            'environment' => $environment,
        ]);

        return Sandbox::fromArray($response['data'] ?? []);
    }

    /**
     * Fetches a single sandbox by id.
     */
    public function get(string $sandbox_id): Sandbox {
        $response = $this->http->request('GET', "/sandboxes/{$sandbox_id}");

        return Sandbox::fromArray($response['data'] ?? []);
    }

    /**
     * Destroys a sandbox, returning its final state.
     */
    public function destroy(string $sandbox_id): Sandbox {
        $response = $this->http->request('DELETE', "/sandboxes/{$sandbox_id}");

        return Sandbox::fromArray($response['data'] ?? []);
    }

    /**
     * Executes a command in a sandbox and waits for it to complete.
     *
     * @param string[] $command
     * @param array<string, string> $environment
     */
    public function exec(
        string $sandbox_id,
        array $command,
        ?string $cwd = null,
        array $environment = [],
        ?string $timeout = null,
    ): SandboxExecResult {
        $response = $this->http->request('POST', "/sandboxes/{$sandbox_id}/exec", json: [
            'command'     => $command,
            'cwd'         => $cwd,
            'environment' => $environment,
            'timeout'     => $timeout,
        ]);

        return SandboxExecResult::fromArray($response['data'] ?? []);
    }

    /**
     * Reads a file from a sandbox's filesystem. The API returns the file
     * as a raw byte stream, not a JSON envelope.
     */
    public function readFile(string $sandbox_id, ?string $path = null): SandboxFileContent {
        $response = $this->http->requestRaw('GET', "/sandboxes/{$sandbox_id}/files", query: [
            'path' => $path,
        ]);

        return new SandboxFileContent($response['body'], $response['content_type']);
    }

    /**
     * Writes a file to a sandbox's filesystem. `content` is raw file
     * content; it is base64-encoded on the wire per the API's byte-field
     * convention.
     */
    public function writeFile(
        string $sandbox_id,
        string $content,
        ?string $path = null,
        ?string $mode = null,
        ?string $content_type = null,
    ): SandboxFileWriteResult {
        $response = $this->http->request('PUT', "/sandboxes/{$sandbox_id}/files", query: [
            'path' => $path,
            'mode' => $mode,
        ], json: [
            'contentType' => $content_type,
            'data'        => base64_encode($content),
        ]);

        return SandboxFileWriteResult::fromArray($response['data'] ?? []);
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function toPaginatedResult(array $response): PaginatedResult {
        return new PaginatedResult(
            items:    array_map(static fn (array $sandbox) => Sandbox::fromArray($sandbox), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
