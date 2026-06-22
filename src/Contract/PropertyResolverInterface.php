<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Contract;

interface PropertyResolverInterface
{
    public function resolve(\ReflectionProperty $property): ?DataFetcherInterface;
}
