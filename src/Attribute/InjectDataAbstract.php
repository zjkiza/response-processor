<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Attribute;

use ZJKiza\ResponseProcessor\Contract\ProcessorAttributeInterface;

abstract class InjectDataAbstract implements ProcessorAttributeInterface
{
    public function __construct(
        public string $fetcher,
    ) {
    }
}
