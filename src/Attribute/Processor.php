<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Processor
{
    public function __construct(
        public string $handler
    ) {}
}
