<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Model\Sandboxes\SandboxLogChunk;
use DealNews\InngestApi\Model\Sandboxes\SandboxProcess;
use DealNews\InngestApi\Model\Sandboxes\SandboxProcessOutput;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for process management, process output, and log streaming within
 * a sandbox: `/sandboxes/{sandboxId}/processes...` and
 * `/sandboxes/{sandboxId}/logs`.
 */
class SandboxProcessesResource extends AbstractResource {

    /**
     * Lists processes running (or that have run) in a sandbox.
     */
    public function list(string $sandbox_id, ?string $cursor = null, int $limit = 50): PaginatedResult {
        $response = $this->http->request('GET', "/sandboxes/{$sandbox_id}/processes", query: [
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return $this->toPaginatedResult($response);
    }

    /**
     * Starts a new process in a sandbox.
     *
     * @param string[] $command
     * @param array<string, string>|null $environment
     */
    public function start(string $sandbox_id, array $command, ?string $cwd = null, ?array $environment = null): SandboxProcess {
        $response = $this->http->request('POST', "/sandboxes/{$sandbox_id}/processes", json: [
            'command'     => $command,
            'cwd'         => $cwd,
            'environment' => $environment,
        ]);

        return SandboxProcess::fromArray($response['data'] ?? []);
    }

    /**
     * Gets a single process running (or that has run) in a sandbox.
     */
    public function get(string $sandbox_id, string $process_id): SandboxProcess {
        $response = $this->http->request('GET', "/sandboxes/{$sandbox_id}/processes/{$process_id}");

        return SandboxProcess::fromArray($response['data'] ?? []);
    }

    /**
     * Gets the buffered output already produced by a process, optionally
     * limited to its trailing bytes.
     */
    public function output(string $sandbox_id, string $process_id, ?int $tail_bytes = null): SandboxProcessOutput {
        $response = $this->http->request('GET', "/sandboxes/{$sandbox_id}/processes/{$process_id}/output", query: [
            'tailBytes' => $tail_bytes,
        ]);

        return SandboxProcessOutput::fromArray($response['data'] ?? []);
    }

    /**
     * Streams a process's output as it is produced, without buffering the
     * whole response in memory.
     *
     * @return \Generator<SandboxLogChunk>
     */
    public function streamOutput(string $sandbox_id, string $process_id, ?int $tail_bytes = null): \Generator {
        $lines = $this->http->stream('GET', "/sandboxes/{$sandbox_id}/processes/{$process_id}/output/stream", query: [
            'tailBytes' => $tail_bytes,
        ]);

        foreach ($lines as $line) {
            yield SandboxLogChunk::fromArray($line['data'] ?? $line);
        }
    }

    /**
     * Sends a signal (e.g. SIGTERM, SIGKILL) to a running process.
     */
    public function signal(string $sandbox_id, string $process_id, int $signal, bool $include_children = false): void {
        $this->http->request('POST', "/sandboxes/{$sandbox_id}/processes/{$process_id}/signals", json: [
            'signal'          => $signal,
            'includeChildren' => $include_children,
        ]);
    }

    /**
     * Blocks server-side until a process exits, then returns its final
     * state.
     */
    public function wait(string $sandbox_id, string $process_id, ?string $timeout = null): SandboxProcess {
        $response = $this->http->request('POST', "/sandboxes/{$sandbox_id}/processes/{$process_id}/wait", query: [
            'timeout' => $timeout,
        ]);

        return SandboxProcess::fromArray($response['data'] ?? []);
    }

    /**
     * Streams a sandbox's logs as they are produced, without buffering the
     * whole response in memory.
     *
     * @return \Generator<SandboxLogChunk>
     */
    public function streamLogs(string $sandbox_id, ?bool $follow = null): \Generator {
        $lines = $this->http->stream('GET', "/sandboxes/{$sandbox_id}/logs", query: [
            'follow' => $follow,
        ]);

        foreach ($lines as $line) {
            yield SandboxLogChunk::fromArray($line['data'] ?? $line);
        }
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function toPaginatedResult(array $response): PaginatedResult {
        return new PaginatedResult(
            items:    array_map(static fn (array $process) => SandboxProcess::fromArray($process), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }
}
