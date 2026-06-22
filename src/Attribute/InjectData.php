<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Attribute;

use Attribute;
use ZJKiza\ResponseProcessor\Contract\ProcessorAttributeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InjectData implements ProcessorAttributeInterface
{
    public function __construct(
        public string $repository,
    ) {}
}
