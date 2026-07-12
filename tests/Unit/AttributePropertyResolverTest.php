<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ZJKiza\ResponseProcessor\Attribute\InjectData;
use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;
use ZJKiza\ResponseProcessor\Handler\AttributePropertyResolver;
use ZJKiza\ResponseProcessor\Handler\FetcherRegistry;
use ZJKiza\ResponseProcessor\Tests\Resources\Attribute\InjectPresenter;

final class TestDummyFetcher implements DataFetcherInterface
{
    public function fetch(iterable $identifiers): array
    {
        return [];
    }
}

final class AttributePropertyResolverTest extends TestCase
{
    public function testResolveReturnsFetcherWhenAttributeMatches(): void
    {
        $fetcher = new TestDummyFetcher();
        $registry = new FetcherRegistry([$fetcher]);
        $resolver = new AttributePropertyResolver(InjectData::class, $registry);

        $object = new class () {
            public function __construct(
                #[InjectData(TestDummyFetcher::class)]
                public mixed $data = null,
            ) {
            }
        };

        $property = new \ReflectionProperty($object, 'data');
        $result = $resolver->resolve($property);

        $this->assertSame($fetcher, $result);
    }

    public function testResolveReturnsNullWhenAttributeDoesNotMatch(): void
    {
        $fetcher = $this->createStub(DataFetcherInterface::class);
        $registry = new FetcherRegistry([$fetcher]);
        $resolver = new AttributePropertyResolver(InjectData::class, $registry);

        $object = new class () {
            public function __construct(
                #[InjectPresenter(\stdClass::class)]
                public mixed $data = null,
            ) {
            }
        };

        $property = new \ReflectionProperty($object, 'data');
        $result = $resolver->resolve($property);

        $this->assertNull($result);
    }

    public function testResolveReturnsNullWhenNoAttribute(): void
    {
        $fetcher = $this->createStub(DataFetcherInterface::class);
        $registry = new FetcherRegistry([$fetcher]);
        $resolver = new AttributePropertyResolver(InjectData::class, $registry);

        $object = new class () {
            public function __construct(
                public mixed $data = null,
            ) {
            }
        };

        $property = new \ReflectionProperty($object, 'data');
        $result = $resolver->resolve($property);

        $this->assertNull($result);
    }

    public function testResolveThrowsWhenFetcherNotInRegistry(): void
    {
        $registry = new FetcherRegistry([]);
        $resolver = new AttributePropertyResolver(InjectData::class, $registry);

        $object = new class () {
            public function __construct(
                #[InjectData('App\NonExistentFetcher')]
                public mixed $data = null,
            ) {
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Data fetcher "App\NonExistentFetcher" is not registered.');

        $property = new \ReflectionProperty($object, 'data');
        $resolver->resolve($property);
    }
}
