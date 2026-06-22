<?php
declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\App;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\TestController;
use ZJKiza\ResponseProcessor\ZJKizaResponseProcessorBundle;

final class ZJKizaResponseProcessorBundleTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return \realpath(__DIR__.'/..'); // @phpstan-ignore-line
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ZJKizaResponseProcessorBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test',
        ]);

        $container->import(__DIR__.'/config/services_test.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('test_all', '/test/all')
            ->controller([TestController::class, 'testAction']);

        $routes->add('test_single', '/test/single')
            ->controller([TestController::class, 'singleAction']);

        $routes->add('test_no_attribute', '/test/no-attribute')
            ->controller([TestController::class, 'noAttributeAction']);
    }
}
