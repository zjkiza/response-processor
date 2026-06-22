<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto;

final class TagDto
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
        // noop.
    }

    public static function create(string $id, string $name): self
    {
        return new self($id, $name);
    }
}
