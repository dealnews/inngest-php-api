<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sandboxes;

use DealNews\InngestApi\Model\Sandboxes\SandboxFileContent;
use PHPUnit\Framework\TestCase;

class SandboxFileContentTest extends TestCase {

    public function testHoldsRawBodyAndContentType(): void {
        $file = new SandboxFileContent('file contents', 'text/plain');

        $this->assertSame('file contents', $file->content);
        $this->assertSame('text/plain', $file->content_type);
    }

    public function testContentTypeDefaultsToNull(): void {
        $file = new SandboxFileContent('file contents');

        $this->assertNull($file->content_type);
    }
}
