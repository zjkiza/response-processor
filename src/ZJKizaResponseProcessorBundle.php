<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use ZJKiza\ResponseProcessor\DependencyInjection\ZJKizaResponseProcessorExtension;

final class ZJKizaResponseProcessorBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new ZJKizaResponseProcessorExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__).'/src';
    }
}
