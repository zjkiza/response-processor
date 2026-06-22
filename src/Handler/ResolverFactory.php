<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Handler;

final readonly class ResolverFactory
{
    public function __construct(
        private FetcherRegistry $fetcherRegistry,
    ) {}

    public function forAttribute(string $attributeClass): AttributePropertyResolver
    {
        return new AttributePropertyResolver($attributeClass, $this->fetcherRegistry);
    }
}
