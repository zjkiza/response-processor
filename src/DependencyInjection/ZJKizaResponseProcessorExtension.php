<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;

final class ZJKizaResponseProcessorExtension extends Extension
{
    public function getAlias(): string
    {
        return 'zjkiza_response_processor';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{listener: array{priority: int}} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'zjkiza_response_processor.listener.priority',
            $config['listener']['priority'],
        );

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config'),
        );

        $loader->load('services.yaml');

        $container
            ->registerForAutoconfiguration(DataFetcherInterface::class)
            ->addTag('zjkiza_response_processor.fetcher');
    }
}
