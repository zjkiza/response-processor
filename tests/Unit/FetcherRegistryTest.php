<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;
use ZJKiza\ResponseProcessor\Handler\FetcherRegistry;

final class FetcherRegistryTest extends TestCase
{
    public function testGetReturnsRegisteredFetcher(): void
    {
        $fetcher = $this->createStub(DataFetcherInterface::class);
        $registry = new FetcherRegistry([$fetcher]);

        $result = $registry->get($fetcher::class);

        $this->assertSame($fetcher, $result);
    }

    public function testGetThrowsOnUnknownFetcher(): void
    {
        $registry = new FetcherRegistry([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Data fetcher "UnknownClass" is not registered.');

        $registry->get('UnknownClass');
    }

    public function testGetWithMultipleFetchers(): void
    {
        $fetcherA = new class implements DataFetcherInterface {
            public function fetch(array $identifiers): array
            {
                return [];
            }
        };

        $fetcherB = new class implements DataFetcherInterface {
            public function fetch(array $identifiers): array
            {
                return [];
            }
        };

        $registry = new FetcherRegistry([$fetcherA, $fetcherB]);

        $this->assertSame($fetcherA, $registry->get($fetcherA::class));
        $this->assertSame($fetcherB, $registry->get($fetcherB::class));
    }

    public function testLaterFetcherOverwritesSameClass(): void
    {
        $fetcherA = $this->createStub(DataFetcherInterface::class);
        $fetcherB = $this->createStub(DataFetcherInterface::class);

        $registry = new FetcherRegistry([$fetcherA, $fetcherB]);

        $this->assertSame($fetcherB, $registry->get($fetcherB::class));
    }
}
