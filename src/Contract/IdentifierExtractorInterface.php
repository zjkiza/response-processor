<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Contract;

interface IdentifierExtractorInterface
{
    public function extract(object $object): ?string;
}
