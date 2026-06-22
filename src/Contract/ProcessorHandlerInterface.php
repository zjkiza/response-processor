<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Contract;

use Symfony\Component\HttpKernel\Event\ViewEvent;

interface ProcessorHandlerInterface
{
    public function process(mixed $data, ViewEvent $event): void;
}
