<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Handler;

use ZJKiza\ResponseProcessor\Contract\IdentifierExtractorInterface;
use ZJKiza\ResponseProcessor\Contract\PropertyResolverInterface;

final class ProcessorEngine
{
    /** @var array<string, list<\ReflectionProperty>> */
    private array $propertyCache = [];
    private int $callSequence = 0;

    public function __construct(
        private readonly IdentifierExtractorInterface $identifierExtractor,
    ) {
    }

    public function process(mixed $data, PropertyResolverInterface $resolver): void
    {
        $items = \is_array($data) ? $data : [$data];
        $objects = \array_filter($items, is_object(...));

        if ([] === $objects) {
            return;
        }

        $identifierMap = [];
        foreach ($objects as $object) {
            $id = $this->identifierExtractor->extract($object);
            if (null !== $id) {
                $identifierMap[$id] = $object;
            }
        }

        if ([] === $identifierMap) {
            return;
        }

        $callId = ++$this->callSequence;

        $identifiers = \array_keys($identifierMap);
        $fetchCache = [];

        foreach ($identifierMap as $id => $object) {
            $properties = $this->resolveProperties($resolver, $callId, $object);

            foreach ($properties as $property) {
                $fetcher = $resolver->resolve($property);

                if (!$fetcher instanceof \ZJKiza\ResponseProcessor\Contract\DataFetcherInterface) {
                    continue;
                }

                $fetcherClass = $fetcher::class;

                if (!isset($fetchCache[$fetcherClass])) {
                    $fetchCache[$fetcherClass] = $fetcher->fetch($identifiers);
                }

                if (!\array_key_exists($id, $fetchCache[$fetcherClass])) {
                    continue;
                }

                $property->setValue($object, $fetchCache[$fetcherClass][$id]);
            }
        }
    }

    /**
     * @return list<\ReflectionProperty>
     */
    private function resolveProperties(PropertyResolverInterface $resolver, int $callId, object $object): array
    {
        $class = $object::class;
        $cacheKey = $callId.'::'.$class;

        if (isset($this->propertyCache[$cacheKey])) {
            return $this->propertyCache[$cacheKey];
        }

        $reflection = new \ReflectionClass($object);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if ($resolver->resolve($property) instanceof \ZJKiza\ResponseProcessor\Contract\DataFetcherInterface) {
                $properties[] = $property;
            }
        }

        $this->propertyCache[$cacheKey] = $properties;

        return $properties;
    }
}
