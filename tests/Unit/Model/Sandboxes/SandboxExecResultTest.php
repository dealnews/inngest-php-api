<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sandboxes;

use DealNews\InngestApi\Model\Sandboxes\SandboxExecResult;
use PHPUnit\Framework\TestCase;

class SandboxExecResultTest extends TestCase {

    public function testFromArrayDecodesBase64StdoutAndStderr(): void {
        $result = SandboxExecResult::fromArray([
            'exitCode' => 1,
            'stdout'   => base64_encode("line one\nline two"),
            'stderr'   => base64_encode('boom'),
            'encoding' => 'utf-8',
        ]);

        $this->assertSame(1, $result->exit_code);
        $this->assertSame("line one\nline two", $result->stdout);
        $this->assertSame('boom', $result->stderr);
        $this->assertSame('utf-8', $result->encoding);
    }

    public function testFromArrayDefaultsToNullsWhenEmpty(): void {
        $result = SandboxExecResult::fromArray([]);

        $this->assertNull($result->exit_code);
        $this->assertNull($result->stdout);
        $this->assertNull($result->stderr);
        $this->assertNull($result->encoding);
    }
}
