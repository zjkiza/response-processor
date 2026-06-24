<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Handler;

use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;
use ZJKiza\ResponseProcessor\Contract\PropertyResolverInterface;

final readonly class AttributePropertyResolver implements PropertyResolverInterface
{
    public function __construct(
        private string $attributeClass,
        private FetcherRegistry $fetcherRegistry,
    ) {
    }

    public function resolve(\ReflectionProperty $property): ?DataFetcherInterface
    {
        $attributes = $property->getAttributes($this->attributeClass);

        if ([] === $attributes) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        /** @var object{fetcher: string} $attribute */
        return $this->fetcherRegistry->get($attribute->fetcher);
    }
}
