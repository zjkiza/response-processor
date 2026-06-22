<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use ZJKiza\ResponseProcessor\DependencyInjection\ZJKizaResponseProcessorExtension;

final class ZJKizaResponseProcessorBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ZJKizaResponseProcessorExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__).'/src';
    }
}
