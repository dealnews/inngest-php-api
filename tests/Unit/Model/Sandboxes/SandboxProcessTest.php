<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sandboxes;

use DealNews\InngestApi\Model\Sandboxes\SandboxProcess;
use DealNews\InngestApi\Model\Sandboxes\SandboxProcessState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SandboxProcessTest extends TestCase {

    public function testFromArrayMapsAllFields(): void {
        $process = SandboxProcess::fromArray([
            'id'                 => 'proc-1',
            'pid'                => 123,
            'command'            => ['sleep', '5'],
            'state'              => 'RUNNING',
            'startedAt'          => '2024-01-01T00:00:00Z',
            'endedAt'            => '2024-01-01T00:00:05Z',
            'exitCode'           => 0,
            'terminationSignal'  => 15,
        ]);

        $this->assertSame('proc-1', $process->id);
        $this->assertSame(123, $process->pid);
        $this->assertSame(['sleep', '5'], $process->command);
        $this->assertSame(SandboxProcessState::Running, $process->state);
        $this->assertNotNull($process->started_at);
        $this->assertNotNull($process->ended_at);
        $this->assertSame(0, $process->exit_code);
        $this->assertSame(15, $process->termination_signal);
    }

    #[DataProvider('stateProvider')]
    public function testFromArrayMapsAllStateValues(string $raw, SandboxProcessState $expected): void {
        $process = SandboxProcess::fromArray(['state' => $raw]);

        $this->assertSame($expected, $process->state);
    }

    /**
     * @return array<string, array{0: string, 1: SandboxProcessState}>
     */
    public static function stateProvider(): array {
        return [
            'unspecified' => ['UNSPECIFIED', SandboxProcessState::Unspecified],
            'starting'    => ['STARTING', SandboxProcessState::Starting],
            'running'     => ['RUNNING', SandboxProcessState::Running],
            'exited'      => ['EXITED', SandboxProcessState::Exited],
            'killed'      => ['KILLED', SandboxProcessState::Killed],
            'failed'      => ['FAILED', SandboxProcessState::Failed],
            'lost'        => ['LOST', SandboxProcessState::Lost],
        ];
    }

    public function testFromArrayDefaultsMissingFieldsToNull(): void {
        $process = SandboxProcess::fromArray([]);

        $this->assertNull($process->id);
        $this->assertNull($process->pid);
        $this->assertSame([], $process->command);
        $this->assertNull($process->state);
        $this->assertNull($process->started_at);
        $this->assertNull($process->ended_at);
        $this->assertNull($process->exit_code);
        $this->assertNull($process->termination_signal);
    }
}
