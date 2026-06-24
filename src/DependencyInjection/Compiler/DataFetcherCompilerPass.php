<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;

final class DataFetcherCompilerPass implements CompilerPassInterface
{
    private const TAG = 'zjkiza_response_processor.fetcher';

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $class = $definition->getClass();

            if (null === $class) {
                continue;
            }

            if (!\is_subclass_of($class, DataFetcherInterface::class)) {
                continue;
            }

            if ($definition->hasTag(self::TAG)) {
                continue;
            }

            $definition->addTag(self::TAG);
        }
    }
}
