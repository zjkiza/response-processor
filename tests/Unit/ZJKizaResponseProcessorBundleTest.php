<?php
declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ZJKiza\ResponseProcessor\ZJKizaResponseProcessorBundle;

final class ZJKizaResponseProcessorBundleTest extends TestCase
{
    public function testGetContainerExtensionReturnsExtension(): void
    {
        $bundle = new ZJKizaResponseProcessorBundle();

        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(
            \ZJKiza\ResponseProcessor\DependencyInjection\ZJKizaResponseProcessorExtension::class,
            $extension,
        );
    }

    public function testGetPathReturnsSourceDirectory(): void
    {
        $bundle = new ZJKizaResponseProcessorBundle();

        $path = $bundle->getPath();

        $this->assertStringEndsWith('/src', $path);
    }
}
