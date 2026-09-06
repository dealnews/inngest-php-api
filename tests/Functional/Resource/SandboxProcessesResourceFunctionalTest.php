<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Sandboxes\SandboxProcess;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class SandboxProcessesResourceFunctionalTest extends FunctionalTestCase {

    public function testListReturnsProcessesForFirstSandbox(): void {
        $sandboxes = $this->skipIfUnavailable(
            fn () => $this->client()->sandboxes()->list(limit: 1)->items,
        );

        if ($sandboxes === []) {
            $this->markTestSkipped('No sandboxes exist in this account.');
        }

        $result = $this->skipIfUnavailable(
            fn () => $this->client()->sandboxProcesses()->list($sandboxes[0]->id, limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $process) {
            $this->assertInstanceOf(SandboxProcess::class, $process);
        }
    }
}
