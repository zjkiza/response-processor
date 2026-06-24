<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Attribute;

use ZJKiza\ResponseProcessor\Contract\ProcessorAttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class InjectData implements ProcessorAttributeInterface
{
    public function __construct(
        public string $fetcher,
    ) {
    }
}
