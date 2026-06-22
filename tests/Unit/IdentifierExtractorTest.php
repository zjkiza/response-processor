<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ZJKiza\ResponseProcessor\Attribute\ProcessorIdentifier;
use ZJKiza\ResponseProcessor\Extractor\ProcessorIdentifierExtractor;

final class IdentifierExtractorTest extends TestCase
{
    private ProcessorIdentifierExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new ProcessorIdentifierExtractor();
    }

    public function testExtractReturnsIdentifier(): void
    {
        $object = new class('test-id') {
            public function __construct(
                #[ProcessorIdentifier]
                public string $id,
            ) {}
        };

        $result = $this->extractor->extract($object);

        $this->assertSame('test-id', $result);
    }

    public function testExtractReturnsNullWhenNoAttribute(): void
    {
        $object = new class('test-id') {
            public function __construct(
                public string $id,
            ) {}
        };

        $result = $this->extractor->extract($object);

        $this->assertNull($result);
    }

    public function testExtractReturnsNullWhenNullValue(): void
    {
        $object = new class(null) {
            public function __construct(
                #[ProcessorIdentifier]
                public ?string $id,
            ) {}
        };

        $result = $this->extractor->extract($object);

        $this->assertNull($result);
    }

    public function testExtractCachesPropertyPerClass(): void
    {
        $objectA = new class('id-a') {
            public function __construct(
                #[ProcessorIdentifier]
                public string $id,
                public string $name = 'a',
            ) {}
        };

        $objectB = new class('b', 'id-b') {
            public function __construct(
                public string $name,
                #[ProcessorIdentifier]
                public string $id,
            ) {}
        };

        $resultA = $this->extractor->extract($objectA);
        $resultB = $this->extractor->extract($objectB);

        $this->assertSame('id-a', $resultA);
        $this->assertSame('id-b', $resultB);
    }

    public function testExtractWithIntIdentifier(): void
    {
        $object = new class(42) {
            public function __construct(
                #[ProcessorIdentifier]
                public int $id,
            ) {}
        };

        $result = $this->extractor->extract($object);

        $this->assertSame('42', $result);
    }
}
